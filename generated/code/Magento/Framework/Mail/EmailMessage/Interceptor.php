<?php
namespace Magento\Framework\Mail\EmailMessage;

/**
 * Interceptor class for @see \Magento\Framework\Mail\EmailMessage
 */
class Interceptor extends \Magento\Framework\Mail\EmailMessage implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Mail\MimeMessageInterface $body, array $to, \Magento\Framework\Mail\MimeMessageInterfaceFactory $mimeMessageFactory, \Magento\Framework\Mail\AddressFactory $addressFactory, ?array $from = null, ?array $cc = null, ?array $bcc = null, ?array $replyTo = null, ?\Magento\Framework\Mail\Address $sender = null, ?string $subject = '', ?string $encoding = 'utf-8')
    {
        $this->___init();
        parent::__construct($body, $to, $mimeMessageFactory, $addressFactory, $from, $cc, $bcc, $replyTo, $sender, $subject, $encoding);
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoding() : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getEncoding');
        if (!$pluginInfo) {
            return parent::getEncoding();
        } else {
            return $this->___callPlugins('getEncoding', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders() : array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getHeaders');
        if (!$pluginInfo) {
            return parent::getHeaders();
        } else {
            return $this->___callPlugins('getHeaders', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFrom() : ?array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getFrom');
        if (!$pluginInfo) {
            return parent::getFrom();
        } else {
            return $this->___callPlugins('getFrom', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTo() : array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTo');
        if (!$pluginInfo) {
            return parent::getTo();
        } else {
            return $this->___callPlugins('getTo', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCc() : ?array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCc');
        if (!$pluginInfo) {
            return parent::getCc();
        } else {
            return $this->___callPlugins('getCc', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBcc() : ?array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getBcc');
        if (!$pluginInfo) {
            return parent::getBcc();
        } else {
            return $this->___callPlugins('getBcc', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReplyTo() : ?array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getReplyTo');
        if (!$pluginInfo) {
            return parent::getReplyTo();
        } else {
            return $this->___callPlugins('getReplyTo', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSender() : ?\Magento\Framework\Mail\Address
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getSender');
        if (!$pluginInfo) {
            return parent::getSender();
        } else {
            return $this->___callPlugins('getSender', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageBody() : \Magento\Framework\Mail\MimeMessageInterface
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getMessageBody');
        if (!$pluginInfo) {
            return parent::getMessageBody();
        } else {
            return $this->___callPlugins('getMessageBody', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyText() : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getBodyText');
        if (!$pluginInfo) {
            return parent::getBodyText();
        } else {
            return $this->___callPlugins('getBodyText', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toString() : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'toString');
        if (!$pluginInfo) {
            return parent::toString();
        } else {
            return $this->___callPlugins('toString', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageType($type)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setMessageType');
        if (!$pluginInfo) {
            return parent::setMessageType($type);
        } else {
            return $this->___callPlugins('setMessageType', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setBody');
        if (!$pluginInfo) {
            return parent::setBody($body);
        } else {
            return $this->___callPlugins('setBody', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setSubject');
        if (!$pluginInfo) {
            return parent::setSubject($subject);
        } else {
            return $this->___callPlugins('setSubject', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getSubject');
        if (!$pluginInfo) {
            return parent::getSubject();
        } else {
            return $this->___callPlugins('getSubject', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getBody');
        if (!$pluginInfo) {
            return parent::getBody();
        } else {
            return $this->___callPlugins('getBody', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($fromAddress)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setFrom');
        if (!$pluginInfo) {
            return parent::setFrom($fromAddress);
        } else {
            return $this->___callPlugins('setFrom', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setFromAddress');
        if (!$pluginInfo) {
            return parent::setFromAddress($fromAddress, $fromName);
        } else {
            return $this->___callPlugins('setFromAddress', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addTo($toAddress)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'addTo');
        if (!$pluginInfo) {
            return parent::addTo($toAddress);
        } else {
            return $this->___callPlugins('addTo', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($ccAddress)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'addCc');
        if (!$pluginInfo) {
            return parent::addCc($ccAddress);
        } else {
            return $this->___callPlugins('addCc', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($bccAddress)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'addBcc');
        if (!$pluginInfo) {
            return parent::addBcc($bccAddress);
        } else {
            return $this->___callPlugins('addBcc', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyToAddress)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setReplyTo');
        if (!$pluginInfo) {
            return parent::setReplyTo($replyToAddress);
        } else {
            return $this->___callPlugins('setReplyTo', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMessage()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getRawMessage');
        if (!$pluginInfo) {
            return parent::getRawMessage();
        } else {
            return $this->___callPlugins('getRawMessage', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyHtml($html)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setBodyHtml');
        if (!$pluginInfo) {
            return parent::setBodyHtml($html);
        } else {
            return $this->___callPlugins('setBodyHtml', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyText($text)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setBodyText');
        if (!$pluginInfo) {
            return parent::setBodyText($text);
        } else {
            return $this->___callPlugins('setBodyText', func_get_args(), $pluginInfo);
        }
    }
}
