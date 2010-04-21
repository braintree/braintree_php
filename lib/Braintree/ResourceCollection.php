<?php
/**
 * Braintree ResourceCollection
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * ResourceCollection is a container object for result data
 *
 * stores and retrieves search results and aggregate data
 *
 * example:
 * <code>
 * $result = Braintree_Customer::all();
 *
 * foreach($result as $transaction) {
 *   print_r($transaction->id);
 * }
 * </code>
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_ResourceCollection implements Iterator
{
    private $_index;
    private $_items;
    private $_currentPageNumber;
    private $_pageSize;
    private $_pager;
    private $_totalItems;
    private $_internalResourceCollection;
    private $_firstResourceCollection;

    /**
     * set up the resource collection
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
        $this->_internalResourceCollection = $this;
        $this->_firstResourceCollection = $this;
    }

    /**
     * returns the current item when iterating with foreach
     */
    public function current()
    {
        return $this->_internalResourceCollection->_items->get($this->_index);
    }

    /**
     * returns the first item in the collection
     *
     * @return mixed
     */
    public function firstItem()
    {
        if ($this->_totalItems == 0)
            return null;
        return $this->_firstResourceCollection->_items->get(0);
    }

    public function key()
    {
        return null;
    }

    /**
     * advances to the next item in the collection when iterating with foreach
     */
    public function next()
    {
        if ($this->_index == count($this->_internalResourceCollection->_items) - 1) {
            $this->_internalResourceCollection = $this->_internalResourceCollection->_nextPage();
            $this->_index = 0;
        } else {
            ++$this->_index;
        }
    }

    /**
     * rewinds the collection to the first item when iterating with foreach
     */
    public function rewind()
    {
        $this->_internalResourceCollection = $this->_firstResourceCollection;
        $this->_index = 0;
    }

    /**
     * returns whether the current item is valid when iterating with foreach
     */
    public function valid()
    {
        return $this->_internalResourceCollection && isset($this->_internalResourceCollection->_items[$this->_index]);
    }

    public function _approximateCount()
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

    /**
     * returns whether the collection is on the last page
     *
     * @return boolean
     */
    private function _isLastPage()
    {
        return ($this->_currentPageNumber == $this->_totalPages());
    }

    /**
     * requests the next page of results for the collection
     *
     * @return none
     */
    private function _nextPage()
    {
        if(!$this->_isLastPage()) {
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
            array_push($methodArgs, array_merge($options, array('page' => $this->_currentPageNumber + 1)));

            // call back to the original creator of the collection
            return call_user_func_array(
                array($className, $classMethod),
                $methodArgs
            );
        } else {
            return false;
        }
    }

    private function _totalPages()
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
}
?>
