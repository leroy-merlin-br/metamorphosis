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

    public function parse($schema, int $id, ?string $subject, ?string $version): self
    {
        $this->setAvroSchema(AvroSchema::parse($schema));
        $this->setSchemaId($id);
        $this->setSubject($subject);
        $this->setVersion($version);

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
