<?php

declare(strict_types=1);

namespace App\EasyAdmin\Field;

use App\Form\JsonEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class JsonField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return new self()
        ->setProperty($propertyName)
        ->setLabel($label)
        ->setFormType(JsonEditorType::class)
        // Add a specific CSS class so we can target it with JavaScript
//        ->addCssClass('js-json-editor')
        ->hideOnIndex();
    }

    public function setJsonSchema(string $jsonSchema): self
    {
        $this->setFormTypeOption('json_schema', $jsonSchema);

        return $this;
    }
}
