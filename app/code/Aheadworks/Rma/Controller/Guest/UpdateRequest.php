<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Controller\GuestAction;
use Aheadworks\Rma\Model\Request\PostDataProcessor\Composite as RequestPostDataProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;

/**
 * Class UpdateRequest
 *
 * @package Aheadworks\Rma\Controller\Guest
 */
class UpdateRequest extends GuestAction
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @var RequestPostDataProcessor
     */
    private $requestPostDataProcessor;

    /**
     * @var RmaRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     * @param FormKeyValidator $formKeyValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param RequestManagementInterface $requestManagement
     * @param RequestPostDataProcessor $requestPostDataProcessor
     * @param RmaRequestInterfaceFactory $requestFactory
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config,
        FormKeyValidator $formKeyValidator,
        DataObjectHelper $dataObjectHelper,
        RequestManagementInterface $requestManagement,
        RequestPostDataProcessor $requestPostDataProcessor,
        RmaRequestInterfaceFactory $requestFactory
    ) {
        parent::__construct($context, $requestRepository, $config);
        $this->formKeyValidator = $formKeyValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestManagement = $requestManagement;
        $this->requestPostDataProcessor = $requestPostDataProcessor;
        $this->requestFactory = $requestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $this->validate();
                $data = $this->requestPostDataProcessor->prepareEntityData($data);
                $requestEntity = $this->performSave($data);
                $this->messageManager->addSuccessMessage(__('Return has been successfully updated.'));
                return $resultRedirect->setPath('*/*/view', ['id' => $requestEntity->getExternalLink()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while updating the return.'));
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate form
     *
     * @throws LocalizedException
     */
    private function validate()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
    }

    /**
     * Perform update
     *
     * @param array $data
     * @return RmaRequestInterface
     */
    private function performSave($data)
    {
        $requestObject = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestObject,
            $data,
            RmaRequestInterface::class
        );
        $requestObject->setId($this->getRmaRequest()->getId());

        return $this->requestManagement->updateRequest($requestObject, false);
    }
}
