<?php
/**
 * Created by JetBrains PhpStorm.
 * User: seb
 * Date: 11/9/12
 * Time: 12:18 PM
 * To change this template use File | Settings | File Templates.
 */

namespace TestStackExample\Task;

class Json2Xml extends AbstractTask
{

    public function run()
    {
        echo PHP_EOL . ' ===== ' . __METHOD__ . ': BEGIN ==== ' . PHP_EOL;

        $data = array(
            'fooInt' => 1234567890,
            'fooBool'=> true,
            'fooString' => 'bar',
            'fooArrayList'=> array(1,2,3,4),
            'fooArrayDictionary' => array(
                'foo' => 'bar',
                'nestedDict' => array(
                    'foo' => 'bar',
                ),
            ),
        );


        $jsonText = json_encode($data);

        echo PHP_EOL . ' ===== ' . __METHOD__ . ': TRY TO SERIALIZE  ... ==== ' . PHP_EOL;

        echo $jsonText;

        $xmlText = $this->json2Xml($jsonText);

        echo PHP_EOL . ' ===== ' . __METHOD__ . ': RESULT ==== ' . PHP_EOL;

        echo $xmlText;

        echo PHP_EOL . ' ===== ' . __METHOD__ . ': END ==== ' . PHP_EOL;

    }


    private function json2Xml($jsonText)
    {
        $serializer = new \Processus\Serializer\XmlRpcValue();
        $serializer->setEncoding('UTF-8');
        $xmlBodyText = $serializer->encode(json_decode($jsonText, true));

        return $xmlBodyText;
    }

}
