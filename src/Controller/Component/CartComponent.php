<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Simple cookie cart component
 */
class CartComponent extends Component
{

    /**
     * Default configuration.
     * @variationFields array with fields of product options
     *
     * @var array
     */
    protected $_defaultConfig = [
        'variationFields' => []
    ];
    
    public $components = [];
    
    /**
     * Array that contains row's id and quantity
     * @var array 
     */
    private $cart = [];
    
    /**
     * Initialize component
     * @param array $config
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $cart = $this->request->getCookie('Cart');
        if (!empty($cart)) {
            $cart = json_decode($cart, true);
        } else {
            $cart = [];
        }
        $this->cart = $cart;
    }
    
    /**
     * Write cart to cookie
     * @param array $cart
     */
    private function set(array $cart)
    {
        $this->cart = $cart;
        
        $this->response = $this->response->withCookie('Cart', [
            'value' => $cart,
            'path' => '/',
            'httpOnly' => true,
            'secure' => false,
            'expire' => strtotime('+1 year')
        ]);
    }
    
    /**
     * Get a specifically key if it possible by variation fields 
     * @param array $variations
     * @return string|false
     */
    private function getVariationKey(array $variations = [])
    {
        $fields = $this->getConfig('variationFields');
        
        if (empty($fields)) {
            $key = false;
        } else {
            $key = [];
            foreach ($fields as $f) {
                $key[$f] = '';
                if (isset($variations[$f])) {
                    $key[$f] = $variations[$f];
                }
            }
            $key = http_build_query($key);
        }
        
        return $key;
    }
    
    /**
     * Return variation fields array
     * @return array
     */
    public function getVariations()
    {
        return $this->getConfig('variationFields');
    }
    
    /**
     * Return $cart array
     * @return array
     */
    public function get()
    {
        return $this->cart;
    }
    
    /**
     * Write to $cart array row's id and quantity or array options with quantity
     * @param int $id
     * @param int $count
     * @param array $variations
     */
    public function put(int $id, int $count, array $variations = [])
    {
        $cart = $this->get();
        
        if ($key = $this->getVariationKey($variations)) {
            $cart[$id][$key] = $count;
        } else {
            $cart[$id] = $count;
        }
        
        $this->set($cart);
    }
    
    /**
     * Increase quantity by one
     * @param int $id
     * @param array $variations
     */
    public function append(int $id, array $variations = [])
    {
        $cart = $this->get();
        
        if ($key = $this->getVariationKey($variations)) {
            if (!isset($cart[$id][$key])) {
                $cart[$id][$key] = 0;
            }
            $cart[$id][$key] += 1;
        } else {
            if (!isset($cart[$id])) {
                $cart[$id] = 0;
            }
            $cart[$id] += 1;
        }
        
        $this->set($cart);
    }
    
    /**
     * Remove by row's id or options
     * @param int $id
     * @param array $variations
     */
    public function remove(int $id, array $variations = [])
    {
        $cart = $this->get();
        
        if ($key = $this->getVariationKey($variations)) {
            unset($cart[$id][$key]);
            if (empty($cart[$id])) {
                unset($cart[$id]);
            }
        } else {
            unset($cart[$id]);
        }
        
        $this->set($cart);
    }
    
    /**
     * Empty $cart array
     */
    public function delete()
    {
        $this->set([]);
    }
}
