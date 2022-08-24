<?php

namespace MageGuide\CatalogListPopup\Controller\Category;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->stockItem = $stockItem;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $toReturn = Array();

        if($this->getRequest()->isAjax()){

            
            $sku             = $this->request->getParam('sku');
            $attribute_code  = $this->request->getParam('attribute_code');
            $attribute_value = $this->request->getParam('value');

            $product            = $this->productRepository->get($sku);
            $availability_msg   = $product->getResource()->getAttribute('cvg_status')->getFrontend()->getValue($product);
            $mg_b2b_qty         = $product->getResource()->getAttribute('mg_b2b_qty')->getFrontend()->getValue($product);
            $mg_b2b_reorder     = $product->getResource()->getAttribute('free_expected')->getFrontend()->getValue($product);
            $current_quantity   = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());


           
            if(mb_strpos($availability_msg, 'Χαμηλή') !== false){
                $availability_msg   = __('Χαμηλή διαθεσιμότητα');
                $availability_class = 'orange';
                $next_stock_msg     = __('Το προϊόν είναι άμεσα διαθέσιμο');
            }else{
                $availability_msg   = __($availability_msg);
                $availability_class = 'green';
                $next_stock_msg     = __('Το προϊόν είναι άμεσα διαθέσιμο');
            }

            if($mg_b2b_qty == 0){
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
                    $availability_msg   = __('Κατόπιν παραγγελίας');
                    $availability_class = 'blue';
                    $next_stock_msg = $this->getStockDate($stock_dates[0]);
                }else{
                    $availability_msg = __('Κατόπιν παραγγελίας');
                    $availability_class = 'blue';
                    $next_stock_msg = '';
                }
            }
            
            $toReturn['status']  = $availability_msg;
            $toReturn['reorder'] = $next_stock_msg;
            $toReturn['addClass']= $availability_class;
            $toReturn['image']   = "/media/catalog/product".$product->getImage();

            //Return JSON
            $result = $this->_resultJsonFactory->create();
            $result->setData($toReturn);
            return $result;
        }
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