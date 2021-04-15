<?php
//! Template filters

class MyFilters {

    static function url($name,$params=array(),$lang=NULL) {
        $f3=Base::instance();
        $ml=Multilang::instance();
        return $f3->BASE.$ml->alias($name,$params,$lang);
    }

}