<?php

declare(strict_types=1);

namespace Cartino\Tests\Feature;

use Cartino\Models\Entry;
use Cartino\Models\User;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create([
            'name' => 'Test Author',
            'email' => 'author@test.com',
        ]);
    }

    /** @test */
    public function it_can_search_entries_by_title()
    {
        Entry::factory()->create([
            'title' => 'Laravel Tutorial',
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        Entry::factory()->create([
            'title' => 'Vue.js Guide',
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search', [
            'q' => 'Laravel',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'results',
                    'breadcrumbs',
                    'filters',
                    'meta',
                    'links',
                ],
            ])
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.results.0.title', 'Laravel Tutorial');
    }

    /** @test */
    public function it_can_filter_by_collection()
    {
        Entry::factory()->create([
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        Entry::factory()->create([
            'collection' => 'news',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search', [
            'collection' => 'blog',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.meta.total', 1);
    }

    /** @test */
    public function it_can_filter_by_tags()
    {
        Entry::factory()->create([
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'data' => ['tags' => ['php', 'laravel']],
            'author_id' => $this->author->id,
        ]);

        Entry::factory()->create([
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'data' => ['tags' => ['javascript', 'vue']],
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search', [
            'tags' => ['php'],
        ]);

        $response->assertStatus(200)->assertJsonPath('data.meta.total', 1);
    }

    /** @test */
    public function it_generates_breadcrumbs()
    {
        Entry::factory()->create([
            'title' => 'Test Entry',
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'data' => ['category' => 'tutorial'],
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search', [
            'collection' => 'blog',
            'category' => 'tutorial',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'breadcrumbs' => [
                        '*' => ['title', 'url', 'active'],
                    ],
                ],
            ]);

        $breadcrumbs = $response->json('data.breadcrumbs');
        $this->assertCount(3, $breadcrumbs);
        $this->assertEquals('Home', $breadcrumbs[0]['title']);
        $this->assertEquals('Blog', $breadcrumbs[1]['title']);
        $this->assertEquals('Tutorial', $breadcrumbs[2]['title']);
    }

    /** @test */
    public function it_returns_available_filters_when_requested()
    {
        Entry::factory()
            ->count(3)
            ->create([
                'collection' => 'blog',
                'status' => 'published',
                'locale' => 'it',
                'author_id' => $this->author->id,
            ]);

        $response = $this->postJson('/api/search', [
            'include_filters' => true,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'filters' => [
                        'collections',
                        'authors',
                        'statuses',
                        'tags',
                        'categories',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_uses_eager_loading_to_prevent_n_plus_one()
    {
        // Create entries with relationships
        Entry::factory()
            ->count(5)
            ->create([
                'collection' => 'blog',
                'status' => 'published',
                'locale' => 'it',
                'author_id' => $this->author->id,
            ]);

        // Enable query log
        \DB::enableQueryLog();

        $response = $this->postJson('/api/search', [
            'per_page' => 5,
        ]);

        $queries = \DB::getQueryLog();

        // Should have:
        // 1. Main entries query
        // 2. Authors eager load
        // 3. Parents eager load
        // 4. Children eager load
        // Total: ~4-5 queries max (not 5+5+5... N+1 problem)
        $this->assertLessThan(10, count($queries), 'Too many queries - possible N+1 issue');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_paginates_results()
    {
        Entry::factory()
            ->count(25)
            ->create([
                'collection' => 'blog',
                'status' => 'published',
                'locale' => 'it',
                'author_id' => $this->author->id,
            ]);

        $response = $this->postJson('/api/search', [
            'per_page' => 10,
            'page' => 2,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.meta.current_page', 2)
            ->assertJsonPath('data.meta.per_page', 10)
            ->assertJsonPath('data.meta.total', 25)
            ->assertJsonPath('data.meta.last_page', 3);
    }

    /** @test */
    public function it_sorts_results()
    {
        Entry::factory()->create([
            'title' => 'B Post',
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        Entry::factory()->create([
            'title' => 'A Post',
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search', [
            'sort' => 'title',
            'sort_direction' => 'asc',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.results.0.title', 'A Post');
    }

    /** @test */
    public function it_defaults_to_published_entries_only()
    {
        Entry::factory()->create([
            'collection' => 'blog',
            'status' => 'published',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        Entry::factory()->create([
            'collection' => 'blog',
            'status' => 'draft',
            'locale' => 'it',
            'author_id' => $this->author->id,
        ]);

        $response = $this->postJson('/api/search');

        $response->assertStatus(200)->assertJsonPath('data.meta.total', 1);
    }
}
