<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class ImportExportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'collection' => 'required|string',
            'mapping' => 'required|array',
        ]);

        try {
            $file = $request->file('file');
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $imported = 0;
            $errors = [];

            foreach ($records as $offset => $record) {
                try {
                    // Process each record based on mapping
                    $mappedData = $this->mapCsvRecord($record, $request->get('mapping'));

                    // Create entry in collection
                    // In real implementation, you would create the actual entry
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$offset}: ".$e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} entries",
                'imported' => $imported,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'collection' => 'required|string',
            'format' => 'in:csv,json',
            'fields' => 'array',
        ]);

        try {
            $collection = $request->get('collection');
            $format = $request->get('format', 'csv');
            $fields = $request->get('fields', []);

            // Mock data - in real implementation, fetch from database
            $entries = [
                [
                    'id' => 1,
                    'title' => 'Premium Wireless Headphones',
                    'price' => 299.99,
                    'stock_quantity' => 25,
                    'status' => 'published',
                ],
                [
                    'id' => 2,
                    'title' => 'Smart Fitness Watch',
                    'price' => 199.99,
                    'stock_quantity' => 0,
                    'status' => 'published',
                ],
            ];

            if ($format === 'csv') {
                $filename = $collection.'_export_'.date('Y-m-d_H-i-s').'.csv';
                $csv = Writer::createFromFileObject(new SplTempFileObject);

                if (! empty($entries)) {
                    // Add headers
                    $headers = ! empty($fields) ? $fields : array_keys($entries[0]);
                    $csv->insertOne($headers);

                    // Add data rows
                    foreach ($entries as $entry) {
                        $row = [];
                        foreach ($headers as $header) {
                            $row[] = $entry[$header] ?? '';
                        }
                        $csv->insertOne($row);
                    }
                }

                return response()->json([
                    'success' => true,
                    'download_url' => '/cp/api/download/'.$filename,
                    'filename' => $filename,
                    'count' => count($entries),
                ]);
            }

            // JSON export
            return response()->json([
                'success' => true,
                'data' => $entries,
                'count' => count($entries),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function downloadExport(string $filename): Response
    {
        // In real implementation, you would serve the actual file
        // For now, generate a sample CSV
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->insertOne(['ID', 'Title', 'Price', 'Stock', 'Status']);
        $csv->insertOne([1, 'Premium Wireless Headphones', 299.99, 25, 'published']);
        $csv->insertOne([2, 'Smart Fitness Watch', 199.99, 0, 'published']);

        $csv->setOutputBOM(Writer::BOM_UTF8);

        return response($csv->toString())
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private function mapCsvRecord(array $record, array $mapping): array
    {
        $mapped = [];

        foreach ($mapping as $csvField => $entryField) {
            if (isset($record[$csvField])) {
                $mapped[$entryField] = $record[$csvField];
            }
        }

        return $mapped;
    }

    public function getImportMapping(Request $request): JsonResponse
    {
        $collection = $request->get('collection');

        // Mock field mapping based on collection
        $fieldMappings = [
            'products' => [
                'title' => 'Title',
                'description' => 'Description',
                'price' => 'Price',
                'stock_quantity' => 'Stock Quantity',
                'sku' => 'SKU',
                'status' => 'Status',
            ],
            'customers' => [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'address' => 'Address',
            ],
        ];

        return response()->json([
            'fields' => $fieldMappings[$collection] ?? [],
            'sample_mappings' => [
                'Title' => 'title',
                'Product Name' => 'title',
                'Price' => 'price',
                'Cost' => 'price',
                'Stock' => 'stock_quantity',
                'Inventory' => 'stock_quantity',
            ],
        ]);
    }
}
