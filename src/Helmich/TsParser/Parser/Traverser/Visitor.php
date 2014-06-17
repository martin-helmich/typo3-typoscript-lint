<?php
namespace Helmich\TsParser\Parser\Traverser;


use Helmich\TsParser\Parser\AST\Statement;

interface Visitor
{



    public function enterTree();



    public function enterNode(Statement $statement);



    public function exitNode(Statement $statement);



    public function exitTree();

}