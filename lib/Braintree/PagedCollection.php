<?php
/**
 * Braintree PagedCollection
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * PagedCollection is a container object for paged result data
 *
 * stores and retrieves search results and aggregate data
 *
 * example:
 * <code>
 * $result = Braintree_Customer::all();
 *
 * // retrieve page 2 of search results
 * $result->nextPage();
 *
 * echo $result->currentPageNumber();
 * // prints 2
 *
 * // get an item
 * $item = $result->firstItem();
 *
 * echo $item['id'];
 * </code>
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_PagedCollection implements Iterator
{
    private $_index;
    private $_items;
    private $_currentPageNumber;
    private $_pageSize;
    private $_pager;
    private $_totalItems;
    private $_internal_paged_collection;
    private $_first_paged_collection;

    /**
     * set up the paged collection
     *
     * expects an array of attributes with literal keys
     *
     * @param array $attributes
     * @param array $pagerAttribs
     */
    public function  __construct($attributes, $pagerAttribs)
    {
        $this->_initializeFromArray($attributes);
        $this->_pager = $pagerAttribs;
        $this->_index = 0;
        $this->_internal_paged_collection = $this;
        $this->_first_paged_collection = $this;
    }

    function rewind()
    {
        $this->_internal_paged_collection = $this->_first_paged_collection;
        $this->_index = 0;
    }

    function current()
    {
        return $this->_internal_paged_collection->_items->get($this->_index);
    }

    function key()
    {
        return null;
    }

    function next()
    {
        if ($this->_index == count($this->_items) - 1) {
            $this->_internal_paged_collection = $this->_internal_paged_collection->nextPage();
            $this->_index = 0;
        } else {
            ++$this->_index;
        }
    }

    function valid()
    {
        return $this->_internal_paged_collection && isset($this->_internal_paged_collection->_items[$this->_index]);
    }

    /**
     * magic function to return inacessible properties
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
           return $this->$name;
        }
        return null;
    }

    /**
     * returns the current page number of the collection
     *
     * @return int
     */
    public function currentPageNumber()
    {
        return $this->_currentPageNumber;
    }

    /**
     * returns whether the collection is on the last page
     *
     * @return boolean
     */
    public function isLastPage()
    {
        return ($this->_currentPageNumber == $this->totalPages());
    }

    /**
     * returns null if on the first page, otherwise previous page number
     *
     * @return mixed
     */
    public function previousPageNumber()
    {
        return ($this->_currentPageNumber == 1) ?
                null :
                $this->_currentPageNumber - 1;
    }

    /**
     * returns the number of items per page, of the collection
     *
     * @return int
     */
    public function pageSize()
    {
        return $this->_pageSize;
    }

    /**
     * requests the next page of results for the collection
     *
     * @return none
     */
    public function nextPage()
    {
        if(!$this->isLastPage()) {
            // extract the method to be called which will return the next page
            $className = $this->_pager['className'];
            $classMethod = $this->_pager['classMethod'];
            if (array_key_exists(1, $this->_pager['methodArgs'])) {
                $options = $this->_pager['methodArgs'][1];
            } else {
                $options = array();
            }
            $methodArgs = array();
            if (array_key_exists(0, $this->_pager['methodArgs'])) {
                array_push($methodArgs, $this->_pager['methodArgs'][0]);
            }
            array_push($methodArgs, array_merge($options, array('page' => $this->nextPageNumber())));

            // call back to the original creator of the collection
            return call_user_func_array(
                array($className, $classMethod),
                $methodArgs
            );
        } else {
            return false;
        }
    }
    /**
     * returns null if on the last page, next page number otherwise
     *
     * @return mixed
     */
    public function nextPageNumber()
    {
        return ($this->isLastPage()) ? null : $this->_currentPageNumber + 1;
    }

    /**
     * calculates total pages in the collection based on page size
     *
     * @return int
     */
    public function totalPages()
    {
        if ($this->_totalItems == 0) {
            return 1;
        }

        $total = intval($this->_totalItems / $this->_pageSize);
        if (($this->_totalItems % $this->_pageSize) != 0) {
            $total += 1;
        }
        return $total;
    }



    /**
     * returns an ArrayIterator for iterating over the items in the collection
     *
     * the collection implements ArrayAccess so looping
     * via foreach, etc is possible.
     *
     * iterator attributes can be accessed via chaining:
     * <code>
     * $item = $pager->items()->current();
     * </code>
     * OR
     * <code>
     * $p = $pager->items();
     * $item = $p->current();
     * </code>
     *
     * @return object ArrayIterator
     */
    public function items()
    {
        return $this->_items;//->getIterator();
    }

    /**
     * returns the first item in the collection
     *
     * @return mixed
     */
    public function firstItem()
    {
        return $this->_items->get(0);
    }

    /**
     * get an item from the collection by index
     *
     * @param int $index
     * @return  mixed item
     */
    public function getItem($index)
    {
        return $this->_items->get($index);
    }

    /**
     * returns total items in the search
     * @return int
     */
    public function totalItems()
    {
        return $this->_totalItems;
    }

    /**
     * initializes instance properties from the keys/values of an array
     * @access protected
     * @param arry $attributes array of properties to set - single level
     * @return none
     */
    private function _initializeFromArray($attributes)
    {
        $this->_items = new Braintree_Collection();

        foreach($attributes AS $key => $attribute) {
            if($key == 'items') {
                foreach($attribute AS $item) {
                    $this->_items->add($item);
                }
            } else {
               $key = "_$key";
               $this->$key = $attribute;
            }
        }
    }
}
?>
