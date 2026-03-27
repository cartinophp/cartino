<?php

declare(strict_types=1);

namespace Cartino\Traits;

trait ExistsAsFile
{
    protected $existsAsFile = false;

    protected $fileExists = false;

    protected $fileLastModified;

    /**
     * Set whether this exists as a file
     *
     * @param  bool  $exists
     * @return $this
     */
    public function existsAsFile($exists = true)
    {
        $this->existsAsFile = $exists;

        return $this;
    }

    /**
     * Check if this exists as a file
     *
     * @return bool
     */
    public function doesExistAsFile()
    {
        return $this->existsAsFile;
    }

    /**
     * Set whether the file exists
     *
     * @param  bool  $exists
     * @return $this
     */
    public function fileExists($exists = true)
    {
        $this->fileExists = $exists;

        return $this;
    }

    /**
     * Check if the file exists
     *
     * @return bool
     */
    public function doesFileExist()
    {
        return $this->fileExists;
    }

    /**
     * Get the file path
     *
     * @return string|null
     */
    public function path()
    {
        return null;
    }

    /**
     * Set file last modified timestamp
     *
     * @param  int  $timestamp
     * @return $this
     */
    public function fileLastModified($timestamp)
    {
        $this->fileLastModified = $timestamp;

        return $this;
    }

    /**
     * Get file last modified timestamp
     *
     * @return int|null
     */
    public function lastModified()
    {
        return $this->fileLastModified;
    }
}
