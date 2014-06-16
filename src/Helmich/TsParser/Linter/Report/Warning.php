<?php
namespace Helmich\TsParser\Linter\Report;


/**
 * Class Warning
 * @package Helmich\TsParser\Linter\Report
 */
class Warning
{



    const SEVERITY_INFO = "info";
    const SEVERITY_WARNING = "warning";
    const SEVERITY_ERROR = "error";


    /** @var int */
    private $line;


    /** @var int */
    private $column;


    /** @var string */
    private $message;


    /** @var string */
    private $severity;


    /** @var string */
    private $source;



    /**
     * @param int    $line
     * @param int    $column
     * @param string $message
     * @param string $severity
     * @param string $source
     */
    public function __construct($line, $column, $message, $severity, $source)
    {
        $this->line     = $line;
        $this->column   = $column;
        $this->message  = $message;
        $this->severity = $severity;
        $this->source   = $source;
    }



    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }



    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }



    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }



    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }



    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }



}