<?php
/**
 * Created by PhpStorm.
 * User: kamilzajac
 * Date: 16/01/15
 * Time: 12:01
 */

namespace Piwik\Plugins\EvenOddReporting\DataTable\Filter;


use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

class SplitEvenOddValues extends BaseFilter
{

    /**
     * Manipulates a {@link DataTable} in some way.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $row = $table->getFirstRow();


    }
}
