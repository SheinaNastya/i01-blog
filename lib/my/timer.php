<?php

class timer
{
   function timer ()
   {
       return true;
   }
      
   function Start_Timer ()
   {
       define ("TIMER_START_TIME", microtime());
       
       return true;
   }
      
   function Get_Time ($decimals=2)
   {
       // $decimals will set the number of decimals you want for your
       // milliseconds.

       // format start time
       $start_time = explode (" ", TIMER_START_TIME);
       $start_time = $start_time[1] + $start_time[0];
       // get and format end time
       $end_time = explode (" ", microtime());
       $end_time = $end_time[1] + $end_time[0];
             
       return number_format ($end_time - $start_time, $decimals);
   }
}

