<?php

declare(strict_types=1);

/*
 * This file is part of the "YAML Field for Symphony CMS" repository.
 *
 * Copyright 2020 Alannah Kearney <hi@alannahkearney.com>
 *
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * A YAML text field.
 */
class FieldYaml extends Field
{
    public function __construct()
    {
        parent::__construct();
        $this->_name = __('Yaml');
        $this->_required = true;

        // Set default
        $this->set('show_column', 'no');
        $this->set('required', 'no');
    }

    /*-------------------------------------------------------------------------
        Definition:
    -------------------------------------------------------------------------*/

    public function canFilter()
    {
        return true;
    }

    /*-------------------------------------------------------------------------
        Setup:
    -------------------------------------------------------------------------*/

    public function createTable()
    {
        return Symphony::Database()->query(
            sprintf(
                'CREATE TABLE `tbl_entries_data_%d` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `entry_id` int(11) unsigned NOT NULL,
                  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
                $this->get('id')
            )
        );
    }

    /*-------------------------------------------------------------------------
        Settings:
    -------------------------------------------------------------------------*/

    public function findDefaults(array &$settings)
    {
        $settings['size'] = $settings['size'] ?? 15;
    }

    public function displaySettingsPanel(XMLElement &$wrapper, $errors = null)
    {
        parent::displaySettingsPanel($wrapper, $errors);

        // Textarea Size
        $label = Widget::Label(__('Number of default rows'));
        $label->setAttribute('class', 'column');
        $input = Widget::Input('fields['.$this->get('sortorder').'][size]', (string) $this->get('size'));
        $label->appendChild($input);

        $div = new XMLElement('div', null, ['class' => 'two columns']);
        $div->appendChild($label);
        $wrapper->appendChild($div);

        // Requirements and table display
        $this->appendStatusFooter($wrapper);
    }

    public function commit()
    {
        if (false == parent::commit() || false == $id = $this->get('id')) {
            return false;
        }

        return FieldManager::saveSettings($id, [
            'size' => $this->get('size'),
        ]);
    }

    /*-------------------------------------------------------------------------
        Publish:
    -------------------------------------------------------------------------*/

    public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null)
    {
        $label = Widget::Label($this->get('label'));

        if ('yes' !== $this->get('required')) {
            $label->appendChild(new XMLElement('i', __('Optional')));
        }

        $value = $data['value'] ?? '';
        $textarea = Widget::Textarea('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, (int) $this->get('size'), 50, (0 != strlen($value) ? General::sanitizeDouble($value) : null));

        /*
         * Allows developers modify the textarea before it is rendered in the publish forms
         *
         * @delegate ModifyTextareaFieldPublishWidget
         * @param string $context
         * '/backend/'
         * @param Field $field
         * @param Widget $label
         * @param Widget $textarea
         */
        Symphony::ExtensionManager()->notifyMembers('ModifyJsonFieldPublishWidget', '/backend/', [
            'field' => &$this,
            'label' => &$label,
            'textarea' => &$textarea,
        ]);

        $label->appendChild($textarea);

        if (null != $flagWithError) {
            $wrapper->appendChild(Widget::Error($label, $flagWithError));
        } else {
            $wrapper->appendChild($label);
        }
    }

    public function checkPostFieldData($data, &$message, $entry_id = null)
    {
        $message = null;

        if ('yes' === $this->get('required') && 0 == strlen(trim((string) $data))) {
            $message = __('‘%s’ is a required field.', [$this->get('label')]);

            return self::__MISSING_FIELDS__;
        }

        try {
            Yaml::parse((string)$data);
        } catch (\Exception $ex) {
            $message = sprintf(
                '‘%s’ contains invalid YAML data. The following error was returned: [%s] <code>%s</code>',
                $this->get('label'),
                $ex->getCode(),
                $ex->getMessage()
            );

            return self::__INVALID_FIELDS__;
        }

        return self::__OK__;
    }

    public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null)
    {
        $status = self::__OK__;

        if (0 == strlen(trim((string) $data))) {
            return [];
        }

        return ['value' => $data];
    }

    /*-------------------------------------------------------------------------
        Output:
    -------------------------------------------------------------------------*/

    public function fetchIncludableElements()
    {
        return [
            $this->get('element_name'),
        ];
    }

    public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null)
    {
        $attributes = [];

        $value = !empty($data['value'])
            ? Yaml::parse($data['value'], 999)
            : $data['value']
        ;

        $buildXmlElementFromArray = function($data, XMLElement $doc) use (&$buildXmlElementFromArray) {

            if(false == is_array($data)) {
                $doc->setValue($data);
            } else {
                $children = [];
                foreach($data as $name => $value) {

                    // The name isn't a string (probably it is an indexed array) so add underscore at start
                    // to produce a valid xml element name
                    if(false == preg_match('@[a-z]@i', (string)$name)) {
                        $name = "_{$name}";
                    }

                    $children[] = $buildXmlElementFromArray($value, new XMLElement($name));
                }
                $doc->appendChildArray($children);
            }

            return $doc;
        };

        $wrapper->appendChild($buildXmlElementFromArray(
            $value,
            new XMLElement($this->get('element_name'))
        ));
    }

    /*-------------------------------------------------------------------------
        Filtering:
    -------------------------------------------------------------------------*/

    public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false)
    {
        $field_id = $this->get('id');

        if (self::isFilterRegex($data[0])) {
            $this->buildRegexSQL($data[0], ['value'], $joins, $where);
        } elseif (self::isFilterSQL($data[0])) {
            $this->buildFilterSQL($data[0], ['value'], $joins, $where);
        } else {
            if (is_array($data)) {
                $data = $data[0];
            }

            ++$this->_key;
            $data = $this->cleanValue($data);
            $joins .= "
                LEFT JOIN
                    `tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
                    ON (e.id = t{$field_id}_{$this->_key}.entry_id)
            ";
            $where .= "
                AND MATCH (t{$field_id}_{$this->_key}.value) AGAINST ('{$data}' IN BOOLEAN MODE)
            ";
        }

        return true;
    }

    /*-------------------------------------------------------------------------
        Events:
    -------------------------------------------------------------------------*/

    public function getExampleFormMarkup()
    {
        $label = Widget::Label($this->get('label'));
        $label->appendChild(Widget::Textarea('fields['.$this->get('element_name').']', (int) $this->get('size'), 50));

        return $label;
    }
}
