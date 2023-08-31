<?php

class rex_mailsprog_list extends rex_list
{
    /**
     * Erstellt den Tabellen Quellcode.
     *
     * @return string
     */
    public function get()
    {
        // Kreatif custom HACK
        // Overwrites parent method to inject custom kreatif EP REX_LIST_TABLE_ROW in rex_list, used for inline editing in yform overview
        // see https://github.com/redaxo/redaxo/pull/5770

        $reflectionProperty = new ReflectionProperty(parent::class, 'tableAttributes');
        $reflectionProperty->setAccessible(true);
        $tableAttributes = $reflectionProperty->getValue($this);

        $reflectionProperty = new ReflectionProperty(parent::class, 'columnDisabled');
        $reflectionProperty->setAccessible(true);
        $columnDisabled = $reflectionProperty->getValue($this);

        $reflectionProperty = new ReflectionProperty(parent::class, 'pager');
        $reflectionProperty->setAccessible(true);
        $pager = $reflectionProperty->getValue($this);

        $reflectionProperty = new ReflectionProperty(parent::class, 'customColumns');
        $reflectionProperty->setAccessible(true);
        $customColumns = $reflectionProperty->getValue($this);

        $reflectionProperty = new ReflectionProperty(parent::class, 'rowAttributes');
        $reflectionProperty->setAccessible(true);
        $rowAttributes = $reflectionProperty->getValue($this);

        $reflectionProperty = new ReflectionProperty(parent::class, 'sql');
        $reflectionProperty->setAccessible(true);
        $sql = $reflectionProperty->getValue($this);

        rex_extension::registerPoint(new rex_extension_point('REX_LIST_GET', $this, [], true));

        $s = "\n";

        // Form vars
        $this->addFormAttribute('action', $this->getUrl([], false));
        $this->addFormAttribute('method', 'post');

        // Table vars
        $caption = $this->getCaption();
        $tableColumnGroups = $this->getTableColumnGroups();
        $class = 'table';
        if (isset($tableAttributes['class'])) {
            $class .= ' ' . $tableAttributes['class'];
        }
        $this->addTableAttribute('class', $class);

        // Columns vars
        $columnFormates = [];
        $columnNames = [];
        foreach ($this->getColumnNames() as $columnName) {
            if (!in_array($columnName, $columnDisabled)) {
                $columnNames[] = $columnName;
            }
        }

        // List vars
        $sortColumn = $this->getSortColumn();
        $sortType = $this->getSortType();
        $warning = $this->getWarning();
        $message = $this->getMessage();
        $nbRows = $this->getRows();

        $header = $this->getHeader();
        $footer = $this->getFooter();

        if ('' != $warning) {
            $s .= rex_view::warning($warning) . "\n";
        } elseif ('' != $message) {
            $s .= rex_view::info($message) . "\n";
        }

        if ('' != $header) {
            $s .= $header . "\n";
        }

        $s .= '<form' . $this->_getAttributeString($this->getFormAttributes()) . '>' . "\n";
        $s .= '    <table' . $this->_getAttributeString($this->getTableAttributes()) . '>' . "\n";

        if ('' != $caption) {
            $s .= '        <caption>' . rex_escape($caption) . '</caption>' . "\n";
        }

        foreach ($tableColumnGroups as $tableColumnGroup) {
            $tableColumns = $tableColumnGroup['columns'];
            unset($tableColumnGroup['columns']);

            $s .= '        <colgroup' . $this->_getAttributeString($tableColumnGroup) . '>' . "\n";

            foreach ($tableColumns as $tableColumn) {
                $s .= '            <col' . $this->_getAttributeString($tableColumn) . ' />' . "\n";
            }

            $s .= '        </colgroup>' . "\n";
        }

        $s .= '        <thead>' . "\n";
        $s .= '            <tr>' . "\n";
        foreach ($columnNames as $columnName) {
            $columnHead = $this->getColumnLabel($columnName);
            if ($this->hasColumnOption($columnName, REX_LIST_OPT_SORT)) {
                if ($columnName == $sortColumn) {
                    $columnSortType = 'desc' == $sortType ? 'asc' : 'desc';
                } else {
                    $columnSortType = $this->getColumnOption($columnName, REX_LIST_OPT_SORT_DIRECTION, 'asc');
                }
                $params = $pager ? [$pager->getCursorName() => $pager->getCursor()] : [];
                $params = array_merge($params, ['sort' => $columnName, 'sorttype' => $columnSortType]);
                $columnHead = '<a class="rex-link-expanded" href="' . $this->getUrl($params) . '">' . $columnHead . '</a>';
            }

            $layout = $this->getColumnLayout($columnName);
            $s .= '        ' . str_replace('###VALUE###', $columnHead, $layout[0]) . "\n";

            // Formatierungen hier holen, da diese Schleife jede Spalte nur einmal durchläuft
            $columnFormates[$columnName] = $this->getColumnFormat($columnName);
        }
        $s .= '            </tr>' . "\n";
        $s .= '        </thead>' . "\n";

        if ('' != $footer) {
            $s .= '        <tfoot>' . "\n";
            $s .= $footer;
            $s .= '        </tfoot>' . "\n";
        }

        if ($nbRows > 0) {
            if ($pager) {
                $maxRows = min($pager->getRowsPerPage(), $nbRows - $pager->getCursor());
            } else {
                $maxRows = $nbRows;
            }

            $rowAttributesCallable = null;
            if (is_callable($rowAttributes)) {
                $rowAttributesCallable = $rowAttributes;
            } elseif ($rowAttributes) {
                $rowAttributes = rex_string::buildAttributes($rowAttributes);
                $rowAttributesCallable = function () use ($rowAttributes) {
                    return $this->replaceVariables($rowAttributes);
                };
            }

            $s .= '        <tbody>' . "\n";
            for ($i = 0; $i < $maxRows; ++$i) {
                $rowAttributes = '';
                if ($rowAttributesCallable) {
                    $rowAttributes = ' ' . $rowAttributesCallable($this);
                }

                $s .= '            <tr' . $rowAttributes . ">\n";
                foreach ($columnNames as $columnName) {
                    $columnValue = $this->formatValue($this->getValue($columnName), $columnFormates[$columnName], !isset($customColumns[$columnName]), $columnName);

                    if (!$this->isCustomFormat($columnFormates[$columnName]) && $this->hasColumnParams($columnName)) {
                        $columnValue = $this->getColumnLink($columnName, $columnValue);
                    }

                    $columnHead = $this->getColumnLabel($columnName);
                    $layout = $this->getColumnLayout($columnName);
                    $columnValue = str_replace(['###VALUE###', '###LABEL###'], [$columnValue, $columnHead], $layout[1]);
                    $columnValue = $this->replaceVariables($columnValue);
                    $s .= '        ' . $columnValue . "\n";
                }
                $s .= '            </tr>' . "\n";
                $s = rex_extension::registerPoint(new rex_extension_point('REX_LIST_TABLE_ROW', $s, [
                    'list' => $this,
                    'sql' => $sql,
                ]));
                $sql->next();
            }
            $s .= '        </tbody>' . "\n";
        } else {
            $s .= '<tr class="table-no-results"><td colspan="' . count($columnNames) . '">' . $this->getNoRowsMessage() . '</td></tr>';
            $s = rex_extension::registerPoint(new rex_extension_point('REX_LIST_TABLE_ROW', $s, [
                'list' => $this,
                'sql' => $sql,
            ]));
        }

        $s .= '    </table>' . "\n";
        $s .= '</form>' . "\n";

        return $s;
    }
}
