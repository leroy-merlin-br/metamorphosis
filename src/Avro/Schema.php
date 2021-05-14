<?php
namespace Metamorphosis\Avro;

use AvroSchema;

class Schema
{
    /**
     * @var string
     */
    private $schemaId;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $version;

    /**
     * @var AvroSchema
     */
    private $avroSchema;

    public function parse($schema, string $id, ?string $subject = null, ?string $version = null): self
    {
        $this->setAvroSchema(AvroSchema::parse($schema));
        $this->setSchemaId($id);
        $this->setSubject($subject);
        $this->setVersion($version);

        return $this;
    }

    public function getSchemaId(): string
    {
        return $this->schemaId;
    }

    public function setSchemaId(string $schemaId): void
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

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }
}
