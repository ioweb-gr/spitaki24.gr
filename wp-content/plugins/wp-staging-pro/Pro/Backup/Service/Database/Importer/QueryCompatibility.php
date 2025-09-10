<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

class QueryCompatibility
{
    public function removeDefiner(&$query)
    {
        if (!stripos($query, 'DEFINER')) {
            return;
        }

        $query = preg_replace('# DEFINER\s?=\s?(.+?(?= )) #i', ' ', $query);
    }

    public function removeSqlSecurity(&$query)
    {
        if (!stripos($query, 'SQL SECURITY')) {
            return;
        }

        $query = preg_replace('# SQL SECURITY \w+ #i', ' ', $query);
    }

    public function removeAlgorithm(&$query)
    {
        if (!stripos($query, 'ALGORITHM')) {
            return;
        }

        $query = preg_replace('# ALGORITHM\s?=\s?`?\w+`? #i', ' ', $query);
    }

    public function replaceTableEngineIfUnsupported(&$query)
    {
        $query = str_ireplace([
            'ENGINE=MyISAM',
            'ENGINE=Aria',
        ], [
            'ENGINE=InnoDB',
            'ENGINE=InnoDB',
        ], $query);
    }

    public function replaceTableRowFormat(&$query)
    {
        $query = str_ireplace([
            'ENGINE=InnoDB',
            'ENGINE=MyISAM',
        ], [
            'ENGINE=InnoDB ROW_FORMAT=DYNAMIC',
            'ENGINE=MyISAM ROW_FORMAT=DYNAMIC',
        ], $query);
    }

    public function removeFullTextIndexes(&$query)
    {
        $query = preg_replace('#,\s?FULLTEXT \w+\s?`?\w+`?\s?\([^)]+\)#i', '', $query);
    }
}
