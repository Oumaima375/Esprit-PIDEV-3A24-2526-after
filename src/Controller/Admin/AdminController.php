<?php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @deprecated Superseded by App\Controller\AdminController
 * Route was removed - it conflicted with app_admin and rendered admin/dashboard.html.twig
 * without the required variables (total, totalCategories, expiresSoon, etc.)
 */
class AdminController extends AbstractController
{
}