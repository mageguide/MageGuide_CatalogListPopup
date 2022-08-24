<?php

namespace MageGuide\CatalogListPopup\Block;

class Stock extends \Magento\Framework\View\Element\Template
{
	protected $_registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,       
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $data = []
    )
    {       
        $this->_registry = $registry;
        $this->productRepository = $productRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getProduct()
    {       
        return $this->_registry->registry('current_product');
    }

    public function getProductId()
    {       
        return $this->getProduct()->getId();
    }

    public function getStockData()
    {
    	$toReturn = array();  
    	$product = $this->getProduct();

    	if($product->getTypeId() == 'configurable'){
    		$children = $product->getTypeInstance()->getUsedProducts($product);
    		foreach($children as $child){
    			$childId = $child->getId();
    			$dates_quantity_raw = $stock_dates = $quantities = array();
    			$cvg_status = $child->getResource()->getAttribute('cvg_status')->getFrontend()->getValue($child);
    			$mg_b2b_qty = $child->getResource()->getAttribute('mg_b2b_qty')->getFrontend()->getValue($child);
    			$mg_b2b_reorder = $child->getResource()->getAttribute('free_expected')->getFrontend()->getValue($child);

    			$toReturn[$childId][0]['qty'] 	 = $mg_b2b_qty;
    			$toReturn[$childId][0]['date'] 	 = 'now';
    			$toReturn[$childId][0]['status'] = $this->getStockStatus($mg_b2b_qty, $mg_b2b_reorder, $cvg_status);

    			if(!empty($mg_b2b_reorder)){
    				$reorder_data_raw = explode(':' , $mg_b2b_reorder);
    				$reorder_data_raw = array_filter($reorder_data_raw);
    				foreach ($reorder_data_raw as $reorder_data) {
	                    $reorder_data = rtrim($reorder_data, ';');
	                    $temp_data_raw = explode(';' , $reorder_data);
	                    foreach ($temp_data_raw as $temp_data) {
	                        $dates_quantity_raw[] = $temp_data;
	                    }
	                }
	                foreach ($dates_quantity_raw as $key => $value) {
	                	if ($key % 2 == 0) {
	                        $stock_dates[] = $value;
	                    } else {
	                        $quantities[] = (int)$value;
	                    }
	                }

	    			for ($index = 0; $index < count($quantities); $index++) {
    					$toReturn[$childId][$index+1]['qty'] 	= $quantities[$index];
		    			$toReturn[$childId][$index+1]['date'] 	= '<span class="date">'.$this->getStockDate($stock_dates[$index]).'</span>';
		    			$toReturn[$childId][$index+1]['status'] = '<span class="availability blue">'.__('Κατόπιν παραγγελίας').'</span>';
	    			}
    			}
    		}
    	}else if($product->getTypeId() == 'simple'){
            $productId = $product->getId();
            $dates_quantity_raw = $stock_dates = $quantities = array();
            $cvg_status = $product->getResource()->getAttribute('cvg_status')->getFrontend()->getValue($product);
            $mg_b2b_qty = $product->getResource()->getAttribute('mg_b2b_qty')->getFrontend()->getValue($product);
            $mg_b2b_reorder = $product->getResource()->getAttribute('free_expected')->getFrontend()->getValue($product);

            $toReturn[$productId][0]['qty']    = $mg_b2b_qty;
            $toReturn[$productId][0]['date']   = 'now';
            $toReturn[$productId][0]['status'] = $this->getStockStatus($mg_b2b_qty, $mg_b2b_reorder, $cvg_status);

            if(!empty($mg_b2b_reorder)){
                $reorder_data_raw = explode(':' , $mg_b2b_reorder);
                $reorder_data_raw = array_filter($reorder_data_raw);
                foreach ($reorder_data_raw as $reorder_data) {
                    $reorder_data = rtrim($reorder_data, ';');
                    $temp_data_raw = explode(';' , $reorder_data);
                    foreach ($temp_data_raw as $temp_data) {
                        $dates_quantity_raw[] = $temp_data;
                    }
                }
                foreach ($dates_quantity_raw as $key => $value) {
                    if ($key % 2 == 0) {
                        $stock_dates[] = $value;
                    } else {
                        $quantities[] = (int)$value;
                    }
                }

                for ($index = 0; $index < count($quantities); $index++) {
                    $toReturn[$productId][$index+1]['qty']    = $quantities[$index];
                    $toReturn[$productId][$index+1]['date']   = '<span class="date">'.$this->getStockDate($stock_dates[$index]).'</span>';
                    $toReturn[$productId][$index+1]['status'] = '<span class="availability blue">'.__('Κατόπιν παραγγελίας').'</span>';
                }
            }
        }
    	
        // $result = $this->_resultJsonFactory->create();
        // $result->setData($toReturn);
        return $toReturn;
    }

    public function getStockStatus($currentB2BQuantity, $reorderB2B, $stockStatus)
    {
        $toReturn = '';
    	if(($currentB2BQuantity > 0)){
            if(mb_strpos($stockStatus, 'Χαμηλή') !== false){
                $toReturn = '<span class="availability orange">'.__('Χαμηλή διαθεσιμότητα').'</span>';
            }else{
               $toReturn = '<span class="availability green">'.__('Άμεσα διαθέσιμο').'</span>';
            }
        }else if (($currentB2BQuantity == 0) && (!empty($reorderB2B))){
            $toReturn = '<span class="availability blue">'.__('Κατόπιν παραγγελίας').'</span>';
        }else if (($currentB2BQuantity == 0) && (empty($reorderB2B))){
            // $dia8esimotita = '<span class="availability red">Εξαντλήθηκε</span>';
            $toReturn = '<span class="availability blue">'.__('Κατόπιν παραγγελίας').'</span>';
        }

        return $toReturn;
    }

    public function getStockDate($date_raw)
    {
    	$toReturn = mb_substr($date_raw, 5);

    	if (mb_strpos($toReturn, 'Ιαν')):
            $toReturn = str_replace('Ιαν', '01', $toReturn);
        elseif (mb_strpos($toReturn, 'Φεβ')):
            $toReturn = str_replace('Φεβ', '02', $toReturn);
        elseif (mb_strpos($toReturn, 'Μαρ')):
            $toReturn = str_replace('Μαρ', '03', $toReturn);
        elseif (mb_strpos($toReturn, 'Απρ')):
            $toReturn = str_replace('Απρ', '04', $toReturn);
        elseif (mb_strpos($toReturn, 'Μαΐ')):
            $toReturn = str_replace('Μαΐ', '05', $toReturn);
        elseif (mb_strpos($toReturn, 'Ιουν')):
            $toReturn = str_replace('Ιουν', '06', $toReturn);
        elseif (mb_strpos($toReturn, 'Ιουλ')):
            $toReturn = str_replace('Ιουλ', '07', $toReturn);
        elseif (mb_strpos($toReturn, 'Αυγ')):
            $toReturn = str_replace('Αυγ', '08', $toReturn);
        elseif (mb_strpos($toReturn, 'Σεπ')):
            $toReturn = str_replace('Σεπ', '09', $toReturn);
        elseif (mb_strpos($toReturn, 'Οκτ')):
            $toReturn = str_replace('Οκτ', '10', $toReturn);
        elseif (mb_strpos($toReturn, 'Νοε')):
            $toReturn = str_replace('Νοε', '11', $toReturn);
        elseif (mb_strpos($toReturn, 'Δεκ')):
            $toReturn = str_replace('Δεκ', '12', $toReturn);
        endif;

    	return $toReturn;
    }
}