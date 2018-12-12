<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/12/12
 * Time: 11:02
 */

namespace app\index\model;


class Invoice extends Common
{
    protected $table = 'invoice';
    protected $pk = 'invoice_id';
}