<?php /* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/* {{{ Version
*  Inflector
*  $Id: inflector.php 101 2005-11-26 10:20:49Z flinn $
*  Copyright (c) Flinn Mueller
*  This file is MIT Licensed - http://www.opensource.org/licenses/mit-license.php
* }}} */

# The Inflector transforms words from singular to plural, class names to table names, modularized class names to ones without,
# and class names to foreign keys.
class ActiveRecordInflector {
    function pluralize($word) {
        $result = strval($word);

        if (in_array(strtolower($result), self::uncountable_words())) {
            return $result;
        } else {
            foreach(self::plural_rules() as $rule => $replacement) {
                if (preg_match($rule, $result)) {
                    $result = preg_replace($rule, $replacement, $result);
                    break;
                }
            }

            return $result;
        }
    }

    function singularize($word) {
        $result = strval($word);

        if (in_array(strtolower($result), self::uncountable_words())) {
            return $result;
        } else {
            foreach(self::singular_rules() as $rule => $replacement) {
                if (preg_match($rule, $result)) {
                    $result = preg_replace($rule, $replacement, $result);
                    break;
                }
            }

            return $result;
        }
    }

    function camelize($lower_case_and_underscored_word) {
        return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", strval($lower_case_and_underscored_word));
    }
  
    function underscore($camel_cased_word) {
        return strtolower(preg_replace('/([A-Z]+)([A-Z])/','\1_\2', preg_replace('/([a-z\d])([A-Z])/','\1_\2', strval($camel_cased_word))));
    }

    function humanize($lower_case_and_underscored_word) {
        return ucfirst(strtolower(ereg_replace('_', " ", strval($lower_case_and_underscored_word))));
    }

    function demodulize($class_name_in_module) {
        return preg_replace('/^.*::/', '', strval($class_name_in_module));
    }

    function tableize($class_name) {
        return self::pluralize(self::underscore($class_name));
    }

    function classify($table_name) {
        return self::camelize(self::singularize($table_name));
    }

    function foreign_key($class_name, $separate_class_name_and_id_with_underscore = true) {
        return self::underscore(self::demodulize($class_name)) .
          ($separate_class_name_and_id_with_underscore ? "_id" : "id");
    }

    function constantize($camel_cased_word=NULL) {
    }

    function uncountable_words() { #:doc
        return array( 'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish' );
    }
  
    function plural_rules() { #:doc:
        return array(
            '/^(ox)$/'                => '\1\2en',     # ox
            '/([m|l])ouse$/'          => '\1ice',      # mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices',     # matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es',       # search, switch, fix, box, process, address
            #'/([^aeiouy]|qu)ies$/'    => '\1y', -- seems to be a bug(?)
            '/([^aeiouy]|qu)y$/'      => '\1ies',      # query, ability, agency
            '/(hive)$/'               => '\1s',        # archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves',    # half, safe, wife
            '/sis$/'                  => 'ses',        # basis, diagnosis
            '/([ti])um$/'             => '\1a',        # datum, medium
            '/(p)erson$/'             => '\1eople',    # person, salesperson
            '/(m)an$/'                => '\1en',       # man, woman, spokesman
            '/(c)hild$/'              => '\1hildren',  # child
            '/(buffal|tomat)o$/'      => '\1\2oes',    # buffalo, tomato
            '/(bu)s$/'                => '\1\2ses',    # bus
            '/(alias|status)/'        => '\1es',       # alias
            '/(octop|vir)us$/'        => '\1i',        # octopus, virus - virus has no defined plural (according to Latin/dictionary.com), but viri is better than viruses/viruss
            '/(ax|cri|test)is$/'      => '\1es',       # axis, crisis
            '/s$/'                    => 's',          # no change (compatibility)
            '/$/'                     => 's'
        );
    }

    function singular_rules() { #:doc:
        return array(
            '/(matr)ices$/'         =>'\1ix',
            '/(vert|ind)ices$/'     => '\1ex',
            '/^(ox)en/'             => '\1',
            '/(alias)es$/'          => '\1',
            '/([octop|vir])i$/'     => '\1us',
            '/(cris|ax|test)es$/'   => '\1is',
            '/(shoe)s$/'            => '\1',
            '/(o)es$/'              => '\1',
            '/(bus)es$/'            => '\1',
            '/([m|l])ice$/'         => '\1ouse',
            '/(x|ch|ss|sh)es$/'     => '\1',
            '/(m)ovies$/'           => '\1\2ovie',
            '/(s)eries$/'           => '\1\2eries',
            '/([^aeiouy]|qu)ies$/'  => '\1y',
            '/([lr])ves$/'          => '\1f',
            '/(tive)s$/'            => '\1',
            '/(hive)s$/'            => '\1',
            '/([^f])ves$/'          => '\1fe',
            '/(^analy)ses$/'        => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/'            => '\1um',
            '/(p)eople$/'           => '\1\2erson',
            '/(m)en$/'              => '\1an',
            '/(s)tatuses$/'         => '\1\2tatus',
            '/(c)hildren$/'         => '\1\2hild',
            '/(n)ews$/'             => '\1\2ews',
            '/s$/'                  => ''
        );
    }
}

?>
