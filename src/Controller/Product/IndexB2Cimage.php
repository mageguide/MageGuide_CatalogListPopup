<?php

namespace MageGuide\CatalogListPopup\Controller\Product;

class IndexB2Cimage extends \Magento\Framework\App\Action\Action
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavModel,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->eavModel = $eavModel;
        $this->stockItem = $stockItem;
        $this->priceCurrency = $priceCurrency;
        $this->imageHelperFactory = $imageHelperFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {       

        if($this->getRequest()->isAjax()){
            
            $toReturn = Array();
            $imageUrl = false;
            $productlink = false;
            $productId = $configProductId = $this->request->getParam('product_id');
            $selectedAttributeData = $this->request->getParam('super_attribute');

            $configProduct = $this->productRepository->getById($configProductId);
            $storeId = $this->_storeManager->getStore()->getId();

            $productTypeInstance = $configProduct->getTypeInstance();
            $productTypeInstance->setStoreFilter($storeId, $configProduct);
            $usedProducts = $productTypeInstance->getUsedProducts($configProduct);

            foreach ($usedProducts as $child) {
                $found = true;
                foreach ($selectedAttributeData as $selectedAttributeId => $selectedOptionId) {
                    $attr = $this->eavModel->load($selectedAttributeId);
                    $attributeCode = $this->eavModel->getAttributeCode();

                    $selectedAttr = $child->getResource()->getAttribute($attributeCode);

                    $value = $child->getAttributeText($attributeCode);
                    $childsOptionId = $selectedAttr->getSource()->getOptionId($value);

                    if($childsOptionId !== $selectedOptionId){
                        $found = false;
                    }
                }
                if($found){
                    $productId = $child->getId();
                    $imageUrl = $this->imageHelperFactory->create()->init($child, 'product_page_image_medium')->getUrl();
                    break;
                }
            }

            $toReturn['id'] = $productId;
            $toReturn['img'] = $imageUrl;

            //Return JSON
            $result = $this->_resultJsonFactory->create();
            $result->setData($toReturn);
            return $result;
        }
    }    
}