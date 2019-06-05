<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/** Controls the Calendar Pages
 * Class CalendarController
 * @package App\Controller
 */
class CalendarController extends AbstractController
{
     /**
      * Displays the calendar and sends shows data
      * @Route("/calendar", name="calendar_home", methods={"GET"})
     */
    public function calendarHome()
    {

        return $this->render('Calendar/calendar.html.twig');
    }
}