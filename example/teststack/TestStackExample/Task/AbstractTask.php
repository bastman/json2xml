<?php
/**
 * Created by JetBrains PhpStorm.
 * User: seb
 * Date: 11/9/12
 * Time: 12:12 PM
 * To change this template use File | Settings | File Templates.
 */

namespace TestStackExample\Task;

class AbstractTask
{

    /**
     *
     */
    public function run()
    {

    }

    /**
     * @param string $promptText
     * @param bool $hideInputEnabled
     * @param string|mixed|null $defaultValue
     * @return string|mixed|null
     */
    protected function _cliPromptInput(
        $promptText,
        $hideInputEnabled,
        $defaultValue
    ) {
        $hideInputEnabled = ($hideInputEnabled === true);
        $promptText = (string)$promptText;

        if (!$hideInputEnabled) {
            echo $promptText;
            $userInput = (string)trim((string)fgets(STDIN));
            if ($userInput === '') {
                $userInput = $defaultValue;
            }

            return $userInput;
        }

        echo $promptText;
        system('stty -echo');
        $userInput = (string)trim((string)fgets(STDIN));
        system('stty echo');
        // add a new line since the users CR didn't echo
        echo "\n";

        if ($userInput === '') {
            $userInput = $defaultValue;
        }

        return $userInput;
    }


}
