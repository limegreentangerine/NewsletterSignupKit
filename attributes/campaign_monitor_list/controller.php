<?php

namespace Concrete\Package\NewsletterSignupKit\Attribute\CampaignMonitorList;

defined('C5_EXECUTE') or die('Access Denied.');

use Core;
use View;
use Doctrine\ORM\EntityManagerInterface;
use ClassKit\Package\Traits\AttributeTrait;
use Concrete\Core\Localization\Localization;
use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList;
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

        $qb = $em->createQueryBuilder()
            ->select('l')
            ->from(CampaignMonitorList::class, 'l')
            ->where('l.showInForms = 1')
            ->orderBy('l.name', 'ASC');

        $site = Core::make('site')->getActiveSiteForEditing();
        $locales = $site->getLocales();
        if (count($locales) > 1) {
            $qb->andWhere('l.languages LIKE :language')
                ->setParameter('language', '%' . Localization::activeLanguage() . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('list');
    }

    public function form()
    {
        return $this->getForm('ccm-attribute-campaign-monitor-list', $this->displayForm($this->getValue()));
    }

    public function getValue()
    {
        return $this->getValueTrait('CampaignMonitorList', 'listId', $this->getAttributeValueID());
    }

    public function getDisplayValue()
    {
        $options = [];
        $values = (array) json_decode($this->getAttributeValue()->getValue());
        $lists = CampaignMonitorList::getByListIDs(array_map('strval', $values));
        foreach ($values as $value) {
            if (isset($lists[$value])) {
                $options[] = $lists[$value]->getName();
            }
        }

        return h(implode(',', $options));
    }

    public function saveValue($listId)
    {
        return $this->saveValueTrait('CampaignMonitorList', $this->getAttributeValueID(), 'listId', $listId);
    }

    public function deleteValue()
    {
        return $this->deleteValueTrait('CampaignMonitorList', $this->getAttributeValueID());
    }

    public function displayForm($value)
    {
        $value = ($value) ? json_decode($value) : [];
        $view = View::getInstance();
        $form = $this->app->make('helper/form');

        foreach ($this->getMailingLists() as $index => $list) {
            $result = in_array($list->getListId(), $value);
            echo '<div class="form-check">';
            echo $form->checkbox($this->field('value') . '[]', $list->getListId(), $result, [ 'id' => $this->field('value') . '_' . $index ]);
            echo $form->label($this->field('value') . '_' . $index, h($list->getName()), [ 'class' => 'form-check-label' ]);
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
