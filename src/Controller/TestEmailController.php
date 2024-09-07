<?php
// src/Controller/TestEmailController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('test@example.com')  // Utilisez une adresse d'expéditeur générique
            ->to('monpierre.aurelien@gmail.com')  // Changez cela pour un destinataire générique, vous-même de préférence
            ->subject('Test Email')
            ->text('This is a test email.');

        try {
            $mailer->send($email);
            return new Response('Test email sent successfully!');
        } catch (\Exception $e) {
            return new Response('Error sending email: ' . $e->getMessage());
        }
    }
}
?>