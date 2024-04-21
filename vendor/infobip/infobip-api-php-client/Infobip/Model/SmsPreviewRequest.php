<?php
/**
 * SmsPreviewRequest
 *
 * PHP version 7.2
 *
 * @category Class
 * @package  Infobip
 * @author   Infobip Support
 * @link     https://www.infobip.com
 */

/**
 * Infobip Client API Libraries OpenAPI Specification
 *
 * OpenAPI specification containing public endpoints supported in client API libraries.
 *
 * Contact: support@infobip.com
 *
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * Do not edit the class manually.
 */

namespace Infobip\Model;

use ArrayAccess;
use Infobip\ObjectSerializer;

/**
 * SmsPreviewRequest Class Doc Comment
 *
 * @category Class
 * @package  Infobip
 * @author   Infobip Support
 * @link     https://www.infobip.com
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class SmsPreviewRequest implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'SmsPreviewRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'languageCode' => 'string',
        'text' => 'string',
        'transliteration' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'languageCode' => null,
        'text' => null,
        'transliteration' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'languageCode' => 'languageCode',
        'text' => 'text',
        'transliteration' => 'transliteration'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'languageCode' => 'setLanguageCode',
        'text' => 'setText',
        'transliteration' => 'setTransliteration'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'languageCode' => 'getLanguageCode',
        'text' => 'getText',
        'transliteration' => 'getTransliteration'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }





    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['languageCode'] = $data['languageCode'] ?? null;
        $this->container['text'] = $data['text'] ?? null;
        $this->container['transliteration'] = $data['transliteration'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!is_null($this->container['languageCode']) && !preg_match("/^(TR|ES|PT|AUTODETECT)$/", $this->container['languageCode'])) {
            $invalidProperties[] = "invalid value for 'languageCode', must be conform to the pattern /^(TR|ES|PT|AUTODETECT)$/.";
        }

        if ($this->container['text'] === null) {
            $invalidProperties[] = "'text' can't be null";
        }
        if (!is_null($this->container['transliteration']) && !preg_match("/^(TURKISH|GREEK|CYRILLIC|SERBIAN_CYRILLIC|CENTRAL_EUROPEAN|BALTIC|NON_UNICODE)$/", $this->container['transliteration'])) {
            $invalidProperties[] = "invalid value for 'transliteration', must be conform to the pattern /^(TURKISH|GREEK|CYRILLIC|SERBIAN_CYRILLIC|CENTRAL_EUROPEAN|BALTIC|NON_UNICODE)$/.";
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets languageCode
     *
     * @return string|null
     */
    public function getLanguageCode()
    {
        return $this->container['languageCode'];
    }

    /**
     * Sets languageCode
     *
     * @param string|null $languageCode Language code for the correct character set. Possible values: `TR` for Turkish, `ES` for Spanish, `PT` for Portuguese, or `AUTODETECT` to let platform select the character set based on message content.
     *
     * @return self
     */
    public function setLanguageCode($languageCode)
    {
        if (!is_null($languageCode) && (!preg_match("/^(TR|ES|PT|AUTODETECT)$/", $languageCode))) {
            throw new \InvalidArgumentException("invalid value for $languageCode when calling SmsPreviewRequest., must conform to the pattern /^(TR|ES|PT|AUTODETECT)$/.");
        }

        $this->container['languageCode'] = $languageCode;

        return $this;
    }

    /**
     * Gets text
     *
     * @return string
     */
    public function getText()
    {
        return $this->container['text'];
    }

    /**
     * Sets text
     *
     * @param string $text Content of the message being sent.
     *
     * @return self
     */
    public function setText($text)
    {
        $this->container['text'] = $text;

        return $this;
    }

    /**
     * Gets transliteration
     *
     * @return string|null
     */
    public function getTransliteration()
    {
        return $this->container['transliteration'];
    }

    /**
     * Sets transliteration
     *
     * @param string|null $transliteration The transliteration of your sent message from one script to another. Transliteration is used to replace characters which are not recognized as part of your defaulted alphabet. Possible values: `TURKISH`, `GREEK`, `CYRILLIC`, `SERBIAN_CYRILLIC`, `CENTRAL_EUROPEAN`, `BALTIC` and `NON_UNICODE`.
     *
     * @return self
     */
    public function setTransliteration($transliteration)
    {
        if (!is_null($transliteration) && (!preg_match("/^(TURKISH|GREEK|CYRILLIC|SERBIAN_CYRILLIC|CENTRAL_EUROPEAN|BALTIC|NON_UNICODE)$/", $transliteration))) {
            throw new \InvalidArgumentException("invalid value for $transliteration when calling SmsPreviewRequest., must conform to the pattern /^(TURKISH|GREEK|CYRILLIC|SERBIAN_CYRILLIC|CENTRAL_EUROPEAN|BALTIC|NON_UNICODE)$/.");
        }

        $this->container['transliteration'] = $transliteration;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}