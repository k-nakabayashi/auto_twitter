<?php

namespace App;
trait Trait_Model 
{
    public function getNo_Duplication() {
        return $this->no_duplication;
    }

    public function getColums_for_Create() {
        return $this->colums_for_create;
    }

    public function getColums_for_Show() {
        return $this->colums_for_show;
    }

    public function getColums_for_Edit() {
        return $this->colums_for_edit;
    }

    public function getColums_for_Destroy() {
        return $this->colums_for_destroy;
    }
}
