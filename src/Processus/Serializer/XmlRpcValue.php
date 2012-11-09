<?php
/**
 * @Experimental
 * quick-n-dirty prove of concept.
 * uses zend-xmlrpc client core logic
 * serializer protocol: http://en.wikipedia.org/wiki/XML-RPC
 *
 * Created by JetBrains PhpStorm.
 * User: seb
 * Date: 11/9/12
 * Time: 12:15 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Processus\Serializer;

use Zend\XmlRpc\AbstractValue;
use Zend\XmlRpc\Generator\GeneratorInterface;

class XmlRpcValue
{

    /**
     * @var array
     */
    private $params;
    /**
     * @var array
     */
    private $xmlRpcParams;
    /**
     * @var array
     */
    private $types;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $xmlText = '';
    /**
     * @var string
     */
    private $xmlBodyText = '';
    /**
     * @var string
     */
    private $xmlDeclarationText = '';

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * @return string
     */
    public function getEncoding()
    {
        return (string)$this->encoding;
    }

    /**
     * @param string $encoding
     * @return XmlRpcValue
     */
    public function setEncoding($encoding)
    {
        $this->encoding = (string)trim((string)$encoding);

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlBodyText()
    {
        return (string)$this->xmlBodyText;
    }

    /**
     * @return string
     */
    public function getXmlDeclarationText()
    {
        return (string)$this->xmlDeclarationText;
    }

    /**
     * @param $value
     * @return string
     */
    public function encode($value)
    {
        $this->init();
        $generator = $this->newGenerator();

        $encoding = (string)trim($this->getEncoding());
        if ($encoding === '') {
            $encoding = 'UTF-8';
        }
        $this->encoding = $encoding;

        $generator->setEncoding(strtoupper($encoding));
        $this->generator = $generator;
        $params = array(
            $value
        );
        $this->serializeParams($params);

        $this->generateValueXml();

        $this->stripDeclaration();

        return $this->getXmlBodyText();
    }


    /**
     * Set the parameters array
     *
     * If called with a single, array value, that array is used to set the
     * parameters stack. If called with multiple values or a single non-array
     * value, the arguments are used to set the parameters stack.
     *
     * Best is to call with array of the format, in order to allow type hinting
     * when creating the XMLRPC values for each parameter:
     * <code>
     * $array = array(
     *     array(
     *         'value' => $value,
     *         'type'  => $type
     *     )[, ... ]
     * );
     * </code>
     *
     * @access public
     * @return void
     */
    private function setParams()
    {
        $argc = func_num_args();
        $argv = func_get_args();
        if (0 == $argc) {
            return;
        }

        if ((1 == $argc) && is_array($argv[0])) {
            $params = array();
            $types = array();
            $wellFormed = true;
            foreach ($argv[0] as $arg) {
                if (!is_array($arg) || !isset($arg['value'])) {
                    $wellFormed = false;
                    break;
                }
                $params[] = $arg['value'];

                if (!isset($arg['type'])) {
                    $xmlRpcValue = AbstractValue::getXmlRpcValue($arg['value']);
                    $arg['type'] = $xmlRpcValue->getType();
                }
                $types[] = $arg['type'];
            }
            if ($wellFormed) {
                $this->xmlRpcParams = $argv[0];
                $this->params = $params;
                $this->types = $types;
            } else {
                $this->params = $argv[0];
                $this->types = array();
                $xmlRpcParams = array();
                foreach ($argv[0] as $arg) {
                    if ($arg instanceof AbstractValue) {
                        $type = $arg->getType();
                    } else {
                        $xmlRpcValue = AbstractValue::getXmlRpcValue($arg);
                        $type = $xmlRpcValue->getType();
                    }
                    $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
                    $this->types[] = $type;
                }
                $this->xmlRpcParams = $xmlRpcParams;
            }

            return;
        }

        $this->params = $argv;
        $this->types = array();
        $xmlRpcParams = array();
        foreach ($argv as $arg) {
            if ($arg instanceof AbstractValue) {
                $type = $arg->getType();
            } else {
                $xmlRpcValue = AbstractValue::getXmlRpcValue($arg);
                $type = $xmlRpcValue->getType();
            }
            $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
            $this->types[] = $type;
        }
        $this->xmlRpcParams = $xmlRpcParams;
    }

    /**
     *
     */
    private function init()
    {
        $this->params = array();
        $this->generator = null;
        $this->types = array();
        $this->xmlRpcParams = array();
        $this->xmlText = '';
        $this->xmlBodyText = '';
        $this->xmlDeclarationText = '';
    }

    /**
     * @param $params
     * @return array
     */
    private function serializeParams($params)
    {

        $validTypes = array(
            AbstractValue::XMLRPC_TYPE_ARRAY,
            AbstractValue::XMLRPC_TYPE_BASE64,
            AbstractValue::XMLRPC_TYPE_BOOLEAN,
            AbstractValue::XMLRPC_TYPE_DATETIME,
            AbstractValue::XMLRPC_TYPE_DOUBLE,
            AbstractValue::XMLRPC_TYPE_I4,
            AbstractValue::XMLRPC_TYPE_INTEGER,
            AbstractValue::XMLRPC_TYPE_NIL,
            AbstractValue::XMLRPC_TYPE_STRING,
            AbstractValue::XMLRPC_TYPE_STRUCT,
        );


        if (!is_array($params)) {
            $params = array($params);
        }
        foreach ($params as $key => $param) {
            if ($param instanceof AbstractValue) {
                continue;
            }

            $type = null;

            if (empty($type) || !in_array($type, $validTypes)) {
                $type = AbstractValue::AUTO_DETECT_TYPE;
            }

            $params[$key] = AbstractValue::getXmlRpcValue($param, $type);
        }

        $this->setParams($params);

        return $params;
    }

    /**
     * Retrieve method parameters as XMLRPC values
     *
     * @return array
     */
    protected function _getXmlRpcParams()
    {
        $params = array();
        if (is_array($this->xmlRpcParams)) {
            foreach ($this->xmlRpcParams as $param) {
                $value = $param['value'];
                $type = $param['type'] ? : AbstractValue::AUTO_DETECT_TYPE;

                if (!$value instanceof AbstractValue) {
                    $value = AbstractValue::getXmlRpcValue($value, $type);
                }
                $params[] = $value;
            }
        }

        return $params;
    }

    /**
     *
     */
    private function generateValueXml()
    {
        $args = $this->_getXmlRpcParams();
        $generator = $this->getGenerator();
        if (is_array($args) && count($args)) {
            foreach ($args as $arg) {
                /**
                 * @var AbstractValue $arg
                 */

                $arg->generateXml();
                break;
            }
        }
        $xmlText = (string)trim((string)$generator->flush());
        $this->xmlText = $xmlText;
    }

    /**
     * @return string
     */
    public function getXmlText()
    {
        return (string)$this->xmlText;
    }


    /**
     * @return GeneratorInterface
     */
    private function newGenerator()
    {
        return AbstractValue::getGenerator();
    }

    /**
     * @return GeneratorInterface
     */
    private function getGenerator()
    {
        if (!$this->generator) {
            $this->generator = $this->newGenerator();
        }

        return $this->generator;
    }


    /**
     *
     */
    private function stripDeclaration()
    {
        $xmlText = (string)trim((string)$this->getXmlText());

        $encoding = (string)$this->getGenerator()->getEncoding();

        $declaration = '<?xml version="1.0" encoding="' . $encoding . '"?>';

        if (strpos($xmlText, $declaration, 0) === 0) {
            $xmlBodyText = (string)str_replace($declaration, '', $xmlText);
            $this->xmlBodyText = (string)trim($xmlBodyText);
            $this->xmlDeclarationText = $declaration;
        } else {
            $this->xmlBodyText = $xmlText;
        }
    }

}

