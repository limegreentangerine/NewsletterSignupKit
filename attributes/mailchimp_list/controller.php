<?php

namespace Concrete\Package\NewsletterSignupKit\Attribute\MailChimpList;

defined('C5_EXECUTE') or die('Access Denied.');

use Doctrine\ORM\EntityManagerInterface;
use ClassKit\Package\Traits\AttributeTrait;
use NewsletterSignupKit\Entity\Mailchimp\MailchimpList;
use Concrete\Core\Attribute\{Controller as AttributeTypeController, FontAwesomeIconFormatter};

class Controller extends AttributeTypeController
{
    use AttributeTrait;

    protected $searchIndexFieldDefinition = [
        'type' => 'string',
        'options' => [
            'length' => 255,
            'default' => null,
            'notnull' => false,
        ],
    ];

    protected function getMailingLists()
    {
        $em = $this->app->make(EntityManagerInterface::class);

        return $em->getRepository(MailchimpList::class)->findBy(['showInForms' => 1], ['name' => 'ASC']);
    }

    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('list');
    }

    public function form()
    {
        return $this->getForm('ccm-attribute-mailchimp-list', $this->displayForm($this->getValue()));
    }

    public function getValue()
    {
        return $this->getValueTrait('MailchimpList', 'listId', $this->getAttributeValueID());
    }

    public function getDisplayValue()
    {
        $options = [];
        $values = (array) json_decode($this->getAttributeValue()->getValue());
        $lists = MailchimpList::getByListIDs(array_map('strval', $values));
        foreach ($values as $value) {
            if (isset($lists[$value])) {
                $options[] = $lists[$value]->getName();
            }
        }

        return h(implode(',', $options));
    }

    public function saveValue($listId)
    {
        return $this->saveValueTrait('MailchimpList', $this->getAttributeValueID(), 'listId', $listId);
    }

    public function deleteValue()
    {
        return $this->deleteValueTrait('MailchimpList', $this->getAttributeValueID());
    }

    public function displayForm($value)
    {
        $value = ($value) ? json_decode($value) : [];
        $form = $this->app->make('helper/form');

        foreach ($this->getMailingLists() as $list) {
            $result = (in_array($list->getListId(), $value)) ? true : false;

            echo '<div class="checkbox">';
            echo '<label>';
            echo $form->checkbox($this->field('value') . '[]', $list->getListId(), $result, ['class' => '']);
            echo h($list->getName());
            echo '</label>';
            echo '</div>';
        }
    }

    public function saveForm($data)
    {
        $allowed = array_map(fn($list) => $list->getListId(), $this->getMailingLists());
        $value = json_encode(array_values(array_intersect((array) ($data['value'] ?? []), $allowed)));
        $this->saveValue($value);
    }
}
