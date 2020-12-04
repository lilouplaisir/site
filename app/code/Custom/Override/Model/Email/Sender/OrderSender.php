<?php
namespace Custom\Override\Model\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
   protected function prepareTemplate(Order $order)
   {
       parent::prepareTemplate($order);

       //Get Payment Method
       $paymentMethod = $order->getPayment()->getMethod();
	   if($order->getCustomerIsGuest() == 1){ 

				     //Define email template for each payment method
				     switch ($paymentMethod) {
					 case 'banktransfer' : $templateId = 4; break;
					 case 'checkmo' : $templateId = 4; break;
					 // Add cases if you have more payment methods
					 default:
						   $templateId = $order->getCustomerIsGuest() ?
							   $this->identityContainer->getGuestTemplateId()
							   : $this->identityContainer->getTemplateId();

				      }
	    } else {
	   
					   //Define email template for each payment method
				       switch ($paymentMethod) {
					   case 'banktransfer' : $templateId = 3; break;
					   case 'checkmo' : $templateId = 3; break;
					   // Add cases if you have more payment methods
					   default:
						   $templateId = $order->getCustomerIsGuest() ?
							   $this->identityContainer->getGuestTemplateId()
							   : $this->identityContainer->getTemplateId();

				       }
	   
	    }

       $this->templateContainer->setTemplateId($templateId);
   }

 }