<?php
namespace Helmich\TypoScriptLint\Util;


class File extends \SplFileObject
{



    public function getContents()
    {
        return file_get_contents($this->getPathname());
    }

}