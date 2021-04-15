<?php
/*=========================================================================*/
/* Name: ClipLine.php                                                      */
/* Uses: Used for clipping a line in a define square                       */
/* Date: 12/7/2007                                                         */
/* Authors:                                                                */
/*   Andrew Que (http://www.DrQue.net/)                                    */
/* References:                                                             */
/*   This unit was constructed mostly from an example in Wikipedia, which  */
/*   had the following credit:                                             */
/*     "Mark S. Sobkow, Paul Pospisil and Yee-Hong Yang. A Fast Two-       */
/*   Dimensional Line Clipping Algorithm via Line Encoding//Computer &     */
/*   Graphics, Vol. 11, No. 4, pp. 459-467, 1987."                         */
/*   The port to PHP was done by Andrew Que                                */
/* Revisions:                                                              */
/*   1.0 - 12/7/2007 - QUE - Creation                                      */
/*   1.1 - 12/31/2007 - QUE - Clipping for complete out-of-bounds          */
/*   1.2 - 2014/05/28 - QUE - Cleanup.                                     */
/*                                                                         */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/* Line clipping function                                                  */
/* Copyright (C) 2007,2014 Andrew Que                                      */
/*                                                                         */
/* This program is free software: you can redistribute it and/or modify    */
/* it under the terms of the GNU General Public License as published by    */
/* the Free Software Foundation, either version 3 of the License, or       */
/* (at your option) any later version.                                     */
/*                                                                         */
/* This program is distributed in the hope that it will be useful,         */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of          */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           */
/* GNU General Public License for more details.                            */
/*                                                                         */
/* You should have received a copy of the GNU General Public License       */
/* along with this program.  If not, see <http://www.gnu.org/licenses/>.   */
/*                                                                         */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/*                         (C) Copyright 2007,2014                         */
/*                               Andrew Que                                */
/*                                   ð|>                                   */
/*=========================================================================*/
/**
 * Used for clipping a line in a define square.
 *
 * This unit was constructed mostly from an example in Wikipedia, which
 * had the following credit:
 * "Mark S. Sobkow, Paul Pospisil and Yee-Hong Yang. A Fast Two-
 * Dimensional Line Clipping Algorithm via Line Encoding//Computer &
 * Graphics, Vol. 11, No. 4, pp. 459-467, 1987."
 * The port to PHP was done by Andrew Que
 *
 * @package XY_Plot
 * @author Andrew Que ({@link http://www.DrQue.net/})
 * @copyright Copyright (c) 2007,2014 Andrew Que
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * Implementation of the Fast-Clipping algorithm.
 *
 * This is a class, but acts more like a function.  The reason
 * implementation is done this way is because of the sub-functions that
 * need access to the local variables.
 */
class ClipLine
{
  public $x;
  public $y;
  public $xx;
  public $yy;

  public $xMin;
  public $yMin;
  public $xMax;
  public $yMax;

  /**
   * Constructor.
   * @param int x Left corner of line
   * @param int y Top corner of line
   * @param int xx Right corner of line
   * @param int yy Bottom corner of line
   * @param int X_Min Left edge of frame
   * @param int Y_Min Top edge of frame
   * @param int X_Max Right edge of frame
   * @param int Y_Max Bottom edge of frame
   */
  public function __construct
    (
     &$x, &$y, &$xx, &$yy,
     $xMin, $yMin, $xMax, $yMax
    )
  {
     // Set local variables
     $this->x     = &$x;
     $this->y     = &$y;
     $this->xx    = &$xx;
     $this->yy    = &$yy;
     $this->X_Min = &$xMin;
     $this->Y_Min = &$yMin;
     $this->X_Max = &$xMax;
     $this->Y_Max = &$yMax;

     // Clip the line
     $this->Clip();
  }

  //------------------------------------------------------
  // Clipping function.
  // (There is one clipping function for each of the 8 areas).
  //------------------------------------------------------

  /**
   * Start top
   */
  private function clipStartTop()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->x += $deltaX * ( $this->Y_Min - $this->y ) / $deltaY;
    $this->y = $this->Y_Min;
  }

  /**
   * Start bottom
   */
  private function clipStartBottom()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->x += $deltaX * ( $this->Y_Max - $this->y ) / $deltaY;
    $this->y = $this->Y_Max;
  }

  /**
   * Start right
   */
  private function clipStartRight()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->y += $deltaY * ( $this->X_Max - $this->x ) / $deltaX;
    $this->x = $this->X_Max;
  }

  /**
   * Start left
   */
  private function clipStartLeft()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->y += $deltaY * ( $this->X_Min - $this->x ) / $deltaX;
    $this->x = $this->X_Min;
  }

  /**
   * End top
   */
  private function clipEndTop()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->xx += $deltaX * ( $this->Y_Min - $this->yy ) / $deltaY;
    $this->yy = $this->Y_Min;
  }

  /**
   * End bottom
   */
  private function clipEndBottom()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->xx += $deltaX * ( $this->Y_Max - $this->yy ) / $deltaY;
    $this->yy = $this->Y_Max;
  }

  /**
   * End right
   */
  private function clipEndRight()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->yy += $deltaY * ( $this->X_Max - $this->xx ) / $deltaX;
    $this->xx = $this->X_Max;
  }

  /**
   * End Left
   */
  private function clipEndLeft()
  {
    $deltaX = $this->xx - $this->x;
    $deltaY = $this->yy - $this->y;
    $this->yy += $deltaY * ( $this->X_Min - $this->xx ) / $deltaX;
    $this->xx = $this->X_Min;
  }

  /**
   * Main body of clipping function
   *
   * This function does all the clipping work
   */
  private function clip()
  {
    $lineCode = 0;

    // Figure out which sides clip
    if ( $this->y < $this->Y_Min )
      $lineCode |= 0x80; // BIT8
    else
    if ( $this->y > $this->Y_Max )
      $lineCode |= 0x40; // BIT7

    if ( $this->x > $this->X_Max )
      $lineCode |= 0x20; // BIT6
    else
    if ( $this->x < $this->X_Min )
      $lineCode |= 0x10; // BIT5

    if ( $this->yy < $this->Y_Min )
      $lineCode |= 0x08; // BIT4
    else
    if ( $this->yy > $this->Y_Max )
      $lineCode |= 0x04; // BIT3

    if ( $this->xx > $this->X_Max )
      $lineCode |= 0x02; // BIT2
    else
    if ( $this->xx < $this->X_Min )
      $lineCode |= 0x01; // BIT1

    // Completely out of bounds? (i.e. not visible on screen)
    if ( ( ( $lineCode & ( 0x80 | 0x08 ) ) == ( 0x80 | 0x08 ) )
      || ( ( $lineCode & ( 0x40 | 0x04 ) ) == ( 0x40 | 0x04 ) )
      || ( ( $lineCode & ( 0x20 | 0x02 ) ) == ( 0x20 | 0x02 ) )
      || ( ( $lineCode & ( 0x10 | 0x01 ) ) == ( 0x10 | 0x01 ) ) )
    {
      $this->x = $this->y = $this->xx = $this->yy = 0;
      return;
    }

    // 9 - 8 - A
    // |   |   |
    // 1 - 0 - 2
    // |   |   |
    // 5 - 4 - 6
    switch ( $lineCode )
    {
      //------------------------------
      // Center
      //------------------------------

      case 0x00:
      {
        // Line is perfect, no clipping needed
        break;
      }

      case 0x01:
      {
        $this->clipEndLeft();
        break;
      }

      case 0x02:
      {
        $this->clipEndRight();
        break;
      }

      case 0x04:
      {
        $this->clipEndBottom();
        break;
      }

      case 0x05:
      {
        $this->clipEndLeft();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      case 0x06:
      {
        $this->clipEndRight();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      case 0x08:
      {
        $this->clipEndTop();
        break;
      }

      case 0x09:
      {
        $this->clipEndLeft();

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        break;
      }

      case 0x0A:
      {
        $this->clipEndRight();

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        break;
      }

      //------------------------------
      // Left
      //------------------------------

      case 0x10:
      {
        $this->clipStartLeft();
        break;
      }

      case 0x12:
      {
        $this->clipStartLeft();
        $this->clipEndRight();
        break;
      }

      case 0x14:
      {
        $this->clipStartLeft();

        if ( $this->y > $this->Y_Max )
          break;

        $this->clipEndBottom();

        break;
      }

      case 0x16:
      {
        $this->clipStartLeft();

        if ( $this->y > $this->Y_Max )
          break;

        $this->clipEndBottom();

        if ( $this->xx > $this->X_Max )
          $this->clipEndRight();

        break;
      }

      case 0x18:
      {
        $this->clipStartLeft();

        if ( $this->y < $this->Y_Min )
          break;

        $this->clipEndTop();

        break;
      }

      case 0x1A:
      {
        $this->clipStartLeft();

        if ( $this->y < $this->Y_Min )
          break;

        $this->clipEndTop();

        if ( $this->xx > $this->X_Max )
          $this->clipEndRight();

        break;
      }

      //------------------------------
      // Right
      //------------------------------

      case 0x20:
      {
        $this->clipStartRight();
        break;
      }

      case 0x21:
      {
        $this->clipStartRight();
        $this->clipEndLeft();
        break;
      }

      case 0x24:
      {
        $this->clipStartRight();

        if ( $this->y > $this->Y_Max )
          break;

        $this->clipEndBottom();

        break;
      }

      case 0x25:
      {
        $this->clipStartRight();
        if ( $this->y > $this->Y_Max )
          break;

        $this->clipEndBottom();

        if ( $this->xx < $this->X_Min )
          $this->clipEndLeft();

        break;
      }

      case 0x28:
      {
        $this->clipStartRight();

        if ( $this->y < $this->Y_Min )
          break;

        $this->clipEndTop();

        break;
      }

      case 0x29:
      {
        $this->clipStartRight();

        if ( $this->y < $this->Y_Min )
          break;

        $this->clipEndTop();

        if ( $this->xx < $this->X_Min )
          $this->clipEndLeft();

        break;
      }

      //------------------------------
      // Bottom
      //------------------------------

      case 0x40:
      {
        $this->clipStartBottom();
        break;
      }

      case 0x41:
      {
        $this->clipStartBottom();

        if ( $this->x < $this->X_Min )
          break;

        $this->clipEndLeft();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      case 0x42:
      {
        $this->clipStartBottom();

        if ( $this->x > $this->X_Max )
          break;

        $this->clipEndRight();

        break;
      }

      case 0x48:
      {
        $this->clipStartBottom();
        $this->clipEndTop();

        break;
      }

      case 0x49:
      {
        $this->clipStartBottom();

        if ( $this->x < $this->X_Min )
          break;

        $this->clipEndLeft();

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        break;
      }

      case 0x4A:
      {
        $this->clipStartBottom();

        if ( $this->x > $this->X_Max )
          break;

        $this->clipEndRight();

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        break;
      }

      //------------------------------
      // Bottom-left
      //------------------------------

      case 0x50:
      {
        $this->clipStartLeft();

        if ( $this->y > $this->Y_Max )
          $this->clipStartBottom();

        break;
      }

      case 0x52:
      {
        $this->clipEndRight();
        if ( $this->yy > $this->Y_Max )
          break;

        $this->clipStartBottom();

        if ( $this->x < $this->X_Min )
          $this->clipStartLeft();

        break;
      }

      case 0x58:
      {
        $this->clipEndTop();

        if ( $this->xx < $this->X_Min )
          break;

        $this->clipStartBottom();

        if ( $this->x < $this->X_Min )
          $this->clipStartLeft();

        break;
      }

      case 0x5A:
      {
        $this->clipStartLeft();

        if ( $this->y < $this->Y_Min )
          break;

        $this->clipEndRight();

        if ( $this->yy > $this->Y_Max )
          break;

        if ( $this->y > $this->Y_Max )
          $this->clipStartBottom();

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        break;
      }

      //------------------------------
      // Bottom-right
      //------------------------------

      case 0x60:
      {
        $this->clipStartRight();

        if ( $this->y > $this->Y_Max )
          $this->clipStartBottom();

        break;
      }

      case 0x61:
      {
        $this->clipEndLeft();

        if ( $this->yy > $this->Y_Max )
          break;

        $this->clipStartBottom();

        if ( $this->x > $this->X_Max )
          $this->clipStartRight();

        break;
      }

      case 0x68:
      {
        $this->clipEndTop();

        if ( $this->xx > $this->X_Max )
          break;

        $this->clipStartRight();

        if ( $this->y > $this->Y_Max )
          $this->clipStartBottom();

        break;
      }

      case 0x69:
      {
        $this->clipEndLeft();

        if ( $this->yy > $this->Y_Max )
          break;

        $this->clipStartRight();

        if ( $this->y < $this->Y_Min )
          break;

        if ( $this->yy < $this->Y_Min )
          $this->clipEndTop();

        if ( $this->y > $this->Y_Max )
          $this->clipStartBottom();

        break;
      }

      //------------------------------
      // Top
      //------------------------------

      case 0x80:
      {
        $this->clipStartTop();
        break;
      }

      case 0x81:
      {
        $this->clipStartTop();

        if ( $this->x < $this->X_Min )
          break;

        $this->clipEndLeft();

        break;
      }

      case 0x82:
      {
        $this->clipStartTop();

        if ( $this->x > $this->X_Max )
          break;

        $this->clipEndRight();

        break;
      }

      case 0x84:
      {
        $this->clipStartTop();
        $this->clipEndBottom();
        break;
      }

      case 0x85:
      {
        $this->clipStartTop();

        if ( $this->x < $this->X_Min )
          break;

        $this->clipEndLeft();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      case 0x86:
      {
        $this->clipStartTop();

        if ( $this->x > $this->X_Max )
          break;

        $this->clipEndRight();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      //------------------------------
      // Top-left
      //------------------------------

      case 0x90:
      {
        $this->clipStartLeft();

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        break;
      }

      case 0x92:
      {
        $this->clipEndRight();

        if ( $this->yy < $this->Y_Min )
          break;

        $this->clipStartTop();

        if ( $this->x < $this->X_Min )
          $this->clipStartLeft();

        break;
      }

      case 0x94:
      {
        $this->clipEndBottom();

        if ( $this->xx < $this->X_Min )
          break;

        $this->clipStartLeft();

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        break;
      }

      case 0x96:
      {
        $this->clipStartLeft();

        if ( $this->y > $this->Y_Max )
          break;

        $this->clipEndRight();

        if ( $this->yy < $this->Y_Min )
          break;

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        break;
      }

      //------------------------------
      // Top-right
      //------------------------------

      case 0xA0:
      {
        $this->clipStartRight();

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        break;
      }

      case 0xA1:
      {
        $this->clipEndLeft();

        if ( $this->yy < $this->Y_Min )
          break;

        $this->clipStartTop();

        if ( $this->x > $this->X_Max )
          $this->clipStartRight();

        break;
      }

      case 0xA4:
      {
        $this->clipEndBottom();

        if ( $this->xx > $this->X_Max )
          break;

        $this->clipStartRight();

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        break;
      }

      case 0xA5:
      {
        $this->clipEndLeft();

        if ( $this->yy < $this->Y_Min )
          break;

        $this->clipStartRight();

        if ( $this->y > $this->Y_Max )
          break;

        if ( $this->yy > $this->Y_Max )
          $this->clipEndBottom();

        if ( $this->y < $this->Y_Min )
          $this->clipStartTop();

        break;
      }
    }

    // Round result
    $this->x  = round( $this->x );
    $this->y  = round( $this->y );
    $this->xx = round( $this->xx );
    $this->yy = round( $this->yy );
  }
}

/**
 * Clip line.
 *
 * This will take the corners of a line segment and clip them in a define
 * area.
 * Examples of use:
 * <code>
 *   $x  = 5;
 *   $y  = 8;
 *   $xx = 70;
 *   $yy = 65;
 *   clipLine(
 *     $x, $y, $xx, $yy,
 *     10, 10, 50, 50 );
 * </code>
 * In this example, the values in <var>$x</var>, <var>$y</var>,
 * <var>$xx</var>, <var>$yy</var> will be adjusted so they are within
 * the (10,10) (50,50) window.
 * @param int x Left corner of line
 * @param int y Top corner of line
 * @param int xx Right corner of line
 * @param int yy Bottom corner of line
 * @param int X_Min Left edge of frame
 * @param int Y_Min Top edge of frame
 * @param int X_Max Right edge of frame
 * @param int Y_Max Bottom edge of frame
 * @return int x, y, xx, yy are all modified (if necessary)
 */
function clipLine
(
  &$x,
  &$y,
  &$xx,
  &$yy,
  $xMin,
  $yMin,
  $xMax,
  $yMax
)
{
  // All of the work is done simply by creating a clip class
  $clip = new
    clipLine
    (
      $x,
      $y,
      $xx,
      $yy,
      $xMin,
      $yMin,
      $xMax,
      $yMax
    );
}

/**
 * Draw clipped line
 *
 * Draw a line that has been clipped into the specified image.
 * Examples of use:
 * <code>
 *   drawClippedLine(
 *     $image,
 *     5, 8, 70, 65,
 *     10, 10, 50, 50,
 *     $black );
 * </code>
 * In this example, a line will be drawn onto <var>$image</var> clipped
 * within the window (10,10) (50,50).
 * @param resource image image to draw line on
 * @param int x Left corner of line
 * @param int y Top corner of line
 * @param int xx Right corner of line
 * @param int yy Bottom corner of line
 * @param int X_Min Left edge of frame
 * @param int Y_Min Top edge of frame
 * @param int X_Max Right edge of frame
 * @param int Y_Max Bottom edge of frame
 * @param resource Color Color of line
 */
function drawClippedLine(
  $image,
  $x,
  $y,
  $xx,
  $yy,
  $xMin,
  $yMin,
  $xMax,
  $yMax,
  $color
)
{
  // Clip this line
  clipLine
  (
    $x,
    $y,
    $xx,
    $yy,
    $xMin,
    $yMin,
    $xMax,
    $yMax
  );

  // Draw the results
  imageLine( $image,  $x, $y, $xx, $yy, $color );
}

// "Those who dream by day are cognizant of many things which escape those
// who dream only by night." - Edgar Allan Poe

?>