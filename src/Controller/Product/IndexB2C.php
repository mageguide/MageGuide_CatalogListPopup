<?php

namespace MageGuide\CatalogListPopup\Controller\Product;

class IndexB2C extends \Magento\Framework\App\Action\Action
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
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->model = $configurableModel;
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

            $productId = $configProductId = $this->request->getParam('product_id');
            $selectedAttributeData = $this->request->getParam('super_attribute');

            $configProduct = $this->productRepository->getById($configProductId);
            
            $childProduct  = $this->model->getProductByAttributes(
			    $selectedAttributeData, $configProduct
			);

			$toReturn['id'] = $childProduct->getId();

            //Return JSON
            $result = $this->_resultJsonFactory->create();
            $result->setData($toReturn);
            return $result;
        }
    }    
}