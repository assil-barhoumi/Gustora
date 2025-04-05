<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/client-dashboard', name: 'client_dashboard')]
    public function clientDashboard()
    {
        return $this->render('dashboard/client.html.twig');
    }

    #[Route('/admin-dashboard', name: 'admin_dashboard')]
    public function adminDashboard()
    {
        return $this->render('dashboard/admin.html.twig');
    }
}
