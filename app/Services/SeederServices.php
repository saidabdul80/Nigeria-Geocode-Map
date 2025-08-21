<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Abstract seeder service for reusable seeding logic
 */
abstract class AbstractSeederService
{
    protected string $model;
    protected array $fillable = [];
    protected int $chunkSize = 1000;
    
    /**
     * Get the data to seed
     */
    abstract protected function getData(): array;
    
    /**
     * Get the model class
     */
    abstract protected function getModelClass(): string;
    
    /**
     * Seed the data
     */
    public function seed(): void
    {
        $model = $this->getModelClass();
        $data = $this->getData();
        
        if (empty($data)) {
            return;
        }
        
        // Check if table exists
        $tableName = (new $model)->getTable();
        if (!Schema::hasTable($tableName)) {
            throw new \Exception("Table {$tableName} does not exist");
        }
        
        // Clear existing data if needed
        if ($this->shouldTruncate()) {
            DB::table($tableName)->truncate();
        }
        
        // Insert data in chunks for better performance
        $chunks = array_chunk($data, $this->chunkSize);
        
        foreach ($chunks as $chunk) {
            $processedChunk = $this->processChunk($chunk);
            DB::table($tableName)->insert($processedChunk);
        }
        
        // Post-processing
        $this->postProcess();
    }
    
    /**
     * Process a chunk of data before insertion
     */
    protected function processChunk(array $chunk): array
    {
        return array_map([$this, 'processRecord'], $chunk);
    }
    
    /**
     * Process a single record
     */
    protected function processRecord(array $record): array
    {
        // Add timestamps if not present
        if (!isset($record['created_at'])) {
            $record['created_at'] = now();
        }
        if (!isset($record['updated_at'])) {
            $record['updated_at'] = now();
        }
        
        return $record;
    }
    
    /**
     * Whether to truncate table before seeding
     */
    protected function shouldTruncate(): bool
    {
        return false;
    }
    
    /**
     * Post-processing after seeding
     */
    protected function postProcess(): void
    {
        // Override in subclasses if needed
    }
    
    /**
     * Set chunk size for batch insertions
     */
    public function setChunkSize(int $size): self
    {
        $this->chunkSize = $size;
        return $this;
    }
}

/**
 * CSV-based seeder service
 */
class CsvSeederService extends AbstractSeederService
{
    protected string $csvPath;
    protected array $columnMapping = [];
    protected bool $hasHeader = true;
    
    public function __construct(string $model, string $csvPath)
    {
        $this->model = $model;
        $this->csvPath = $csvPath;
    }
    
    protected function getData(): array
    {
        if (!file_exists($this->csvPath)) {
            throw new \Exception("CSV file not found: {$this->csvPath}");
        }
        
        $handle = fopen($this->csvPath, 'r');
        $data = [];
        $isFirstRow = true;
        $headers = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->hasHeader && $isFirstRow) {
                $headers = $row;
                $isFirstRow = false;
                continue;
            }
            
            if ($this->hasHeader && !empty($headers)) {
                $record = array_combine($headers, $row);
            } else {
                $record = $row;
            }
            
            $data[] = $this->mapColumns($record);
        }
        
        fclose($handle);
        return $data;
    }
    
    protected function getModelClass(): string
    {
        return $this->model;
    }
    
    /**
     * Set column mapping for CSV columns to database columns
     */
    public function setColumnMapping(array $mapping): self
    {
        $this->columnMapping = $mapping;
        return $this;
    }
    
    /**
     * Set whether CSV has header row
     */
    public function setHasHeader(bool $hasHeader): self
    {
        $this->hasHeader = $hasHeader;
        return $this;
    }
    
    /**
     * Map CSV columns to database columns
     */
    protected function mapColumns(array $record): array
    {
        if (empty($this->columnMapping)) {
            return $record;
        }
        
        $mapped = [];
        foreach ($this->columnMapping as $csvColumn => $dbColumn) {
            if (isset($record[$csvColumn])) {
                $mapped[$dbColumn] = $record[$csvColumn];
            }
        }
        
        return $mapped;
    }
}

/**
 * JSON-based seeder service
 */
class JsonSeederService extends AbstractSeederService
{
    protected string $jsonPath;
    
    public function __construct(string $model, string $jsonPath)
    {
        $this->model = $model;
        $this->jsonPath = $jsonPath;
    }
    
    protected function getData(): array
    {
        if (!file_exists($this->jsonPath)) {
            throw new \Exception("JSON file not found: {$this->jsonPath}");
        }
        
        $content = file_get_contents($this->jsonPath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON: " . json_last_error_msg());
        }
        
        return is_array($data) ? $data : [];
    }
    
    protected function getModelClass(): string
    {
        return $this->model;
    }
}

/**
 * Array-based seeder service (for backward compatibility)
 */
class ArraySeederService extends AbstractSeederService
{
    protected array $data;
    
    public function __construct(string $model, array $data)
    {
        $this->model = $model;
        $this->data = $data;
    }
    
    protected function getData(): array
    {
        return $this->data;
    }
    
    protected function getModelClass(): string
    {
        return $this->model;
    }
}

/**
 * Factory for creating seeder services
 */
class SeederServiceFactory
{
    /**
     * Create seeder from CSV file
     */
    public static function fromCsv(string $model, string $csvPath): CsvSeederService
    {
        return new CsvSeederService($model, $csvPath);
    }
    
    /**
     * Create seeder from JSON file
     */
    public static function fromJson(string $model, string $jsonPath): JsonSeederService
    {
        return new JsonSeederService($model, $jsonPath);
    }
    
    /**
     * Create seeder from array data
     */
    public static function fromArray(string $model, array $data): ArraySeederService
    {
        return new ArraySeederService($model, $data);
    }
}