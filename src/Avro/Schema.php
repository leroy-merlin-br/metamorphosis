<?php
namespace Metamorphosis\Avro;

use AvroSchema;

class Schema
{
    /**
     * @var int
     */
    private $schemaId;

    /**
     * @var AvroSchema
     */
    private $avroSchema;

    public function parse($schema, $id): self
    {
        $this->avroSchema = AvroSchema::parse($schema);
        $this->schemaId = $id;

        return $this;
    }

    public function getSchemaId(): int
    {
        return $this->schemaId;
    }

    public function setSchemaId(int $schemaId): void
    {
        $this->schemaId = $schemaId;
    }

    public function getAvroSchema(): AvroSchema
    {
        return $this->avroSchema;
    }

    public function setAvroSchema(AvroSchema $avroSchema): void
    {
        $this->avroSchema = $avroSchema;
    }
}
