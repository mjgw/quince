<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * AtomElement class for XML_Feed_Parser package
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   XML
 * @package    XML_Feed_Parser
 * @author     James Stewart <james@jystewart.net>
 * @copyright  2005 James Stewart <james@jystewart.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU LGPL 2.1
 * @version    CVS: $Id: AtomElement.php,v 1.12 2006/01/06 03:57:31 jystewart Exp $
 * @link       http://pear.php.net/package/XML_Feed_Parser/
 */

/**
 * This class provides support for atom entries. It will usually be called by
 * XML_Feed_Parser_Atom with which it shares many methods.
 *
 * @author    James Stewart <james@jystewart.net>
 * @version    Release: @package_version@
 * @package XML_Feed_Parser
 */
class XML_Feed_Parser_AtomElement extends XML_Feed_Parser_Atom
{
    /**
     * This will be a reference to the parent object for when we want
     * to use a 'fallback' rule 
     * @var XML_Feed_Parser_Atom
     */
    protected $parent;

    /**
     * When performing XPath queries we will use this prefix 
     * @var string
     */
    private $xpathPrefix = '';
    
    /**
     * xml:base values inherited by the element 
     * @var string
     */
    protected $xmlBase;

    /**
     * Here we provide a few mappings for those very special circumstances in
     * which it makes sense to map back to the RSS2 spec or to manage other
     * compatibilities (eg. with the Univeral Feed Parser). Key is the other version's
     * name for the command, value is an array consisting of the equivalent in our atom 
     * api and any attributes needed to make the mapping.
     * @var array
     */
    protected $compatMap = array(
        'guid' => array('id'),
        'links' => array('link'),
        'tags' => array('category'),
        'contributors' => array('contributor'));
        
    /**
     * Our specific element map 
     * @var array
     */
    protected $map = array(
        'author' => array('Person', 'fallback'),
        'contributor' => array('Person'),
        'id' => array('Text', 'fail'),
        'published' => array('Date'),
        'updated' => array('Date', 'fail'),
        'title' => array('Text', 'fail'),
        'rights' => array('Text', 'fallback'),
        'summary' => array('Text'),
        'content' => array('Content'),
        'link' => array('Link'),
        'enclosure' => array('Enclosure'),
        'category' => array('Category'));

    /**
     * Store useful information for later.
     *
     * @param   DOMElement  $element - this item as a DOM element
     * @param   XML_Feed_Parser_Atom    $parent - the feed of which this is a member
     */
    function __construct(DOMElement $element, $parent, $xmlBase = '')
    {
        $this->model = $element;
        $this->parent = $parent;
        $this->xmlBase = $xmlBase;
        $this->xpathPrefix = "//atom:entry[atom:id='" . $this->id . "']/";
    }

    /**
     * author data at the entry level is more complex than at the feed level.
     * If atom:author is not present for the entry we need to look for it in
     * an atom:source child of the atom:entry. If it's not there either, then
     * we look to the parent for data.
     *
     * @param   array
     * @return  string
     */
    function getAuthor($arguments)
    {
        /* Find out which part of the author data we're looking for */
        if (isset($arguments['param'])) {
            $parameter = $arguments['param'];
        } else {
            $parameter = 'name';
        }
        
        $test = $this->model->getElementsByTagName('author');
        if ($test->length > 0) {
            $item = $test->item(0);
            return $item->getElementsByTagName($parameter)->item(0)->nodeValue;
        }
        
        $source = $this->model->getElementsByTagName('source');
        if ($source->length > 0) {
            $test = $this->model->getElementsByTagName('author');
            if ($test->length > 0) {
                $item = $test->item(0);
                return $item->getElementsByTagName($parameter)->item(0)->nodeValue;
            }
        }
        return $this->parent->getAuthor($arguments);
    }

    /**
     * This element may or may not be present. It cannot be present more than
     * once. It may have a 'src' attribute, in which case there's no content
     * If not present, then the entry must have link with rel="alternate".
     * If there is content we return it, if not and there's a 'src' attribute
     * we return the value of that instead. The method can take an 'attribute'
     * argument, in which case we return the value of that attribute if present.
     * eg. $item->content("type") will return the type of the content. It is
     * recommended that all users check the type before getting the content to
     * ensure that their script is capable of handling the type of returned data.
     * (data carried in the content element can be either 'text', 'html', 'xhtml', 
     * or any standard MIME type).
     *
     * @todo    Work out overlap with general text construct
     * @return  string|false
     */
    function getContent($method, $arguments = array())
    {
        $offset = empty($arguments[0]) ? false : $arguments[0];
        $attribute = empty($arguments[1]) ? false : $arguments[1];
        $tags = $this->model->getElementsByTagName('content');

        if ($tags->length == 0) {
            return false;
        }

        $content = $tags->item(0);

        if (! $content->hasAttribute('type')) {
            $content->setAttribute('type', 'text');
        }
        $type = $content->getAttribute('type');

        if (! empty($attribute)) {
            if ($content->hasAttribute($attribute))
            {
                return $content->getAttribute($attribute);
            }
            return false;
        }

        if ($content->hasAttribute('src')) {
            return $content->getAttribute('src');
        }

        switch ($type) {
            case 'application/octet-stream':
                return base64_decode(trim($content->nodeValue));
                break;
            case 'text/plain':
            case 'text':
                return $content->nodeValue;
                break;
            case 'html':
                return str_replace('&lt;', '<', $content->nodeValue);
                break;
            case 'xhtml':
                $container = $content->getElementsByTagName('div');
                if ($container->length == 0) {
                    return false;
                }
                $contents = $container->item(0);
                if ($contents->hasChildNodes()) {
                    /* Iterate through, applying xml:base and store the result */
                    $result = '';
                    foreach ($contents->childNodes as $node) {
                        $result .= $this->traverseNode($node);
                    }
                    return $result;
                }
                break;
            default:
                break;
        }
        return false;
     }

    /**
     * The Atom spec doesn't provide for an enclosure element, but it is
     * generally supported using the link element with rel='enclosure'.
     *
     * @param   string  $method - for compatibility with our __call usage
     * @param   array   $arguments - for compatibility with our __call usage
     * @return  array|false
     */
    function getEnclosure($method, $arguments = array())
    {
        $offset = isset($arguments[0]) ? $arguments[0] : 0;
        $query = "//atom:entry[atom:id='" . $this->getText('id', false) . 
            "']/atom:link[@rel='enclosure']";

        $encs = $this->parent->xpath->query($query);
        if ($encs->length > 0 and $encs->length >= $offset) {
            try {
                $attrs = $encs->item($offset)->attributes;
                $length = $encs->item($offset)->hasAttribute('length') ? 
                    $encs->item($offset)->getAttribute('length') : false;
                return array(
                    'url' => $attrs->getNamedItem('href')->value,
                    'type' => $attrs->getNamedItem('type')->value,
                    'length' => $length);
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
    
    /**
     * Where an atom:entry is taken from another feed then the aggregator
     * is supposed to include an atom:source element which replicates at least
     * the atom:id, atom:title, and atom:updated metadata from the original
     * feed. Atom:source therefore has a very similar structure to atom:feed
     * and if we find it we will return it as an XML_Feed_Parser_Atom object.
     *
     * @return  XML_Feed_Parser_Atom|false
     */
    function getSource()
    {
        $test = $this->model->getElementsByTagName('source');
        if ($test->length == 0) {
            return false;
        }
        $source = new XML_Feed_Parser_Atom($test->item(0));
    }

    /**
     * Return an XML serialization of the feed, should it be required. Most 
     * users however, will already have a serialization that they used when 
     * instantiating the object.
     *
     * @return    string    XML serialization of element
     */    
    function __toString()
    {
        $simple = simplexml_import_dom($this->model);
        return $simple->asXML();
    }
}

?>