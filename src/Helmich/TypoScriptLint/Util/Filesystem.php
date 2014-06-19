<?php
namespace Helmich\TypoScriptLint\Util;


class Filesystem
{



    /**
     * @param string $filename
     * @return \Helmich\TypoScriptLint\Util\File
     */
    public function openFile($filename)
    {
        return new File($filename);
    }

}