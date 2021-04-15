<?php
/*=========================================================================*/
/* Name: ClipCircle.php                                                    */
/* Uses: Draw a circle clipped within boundaries                           */
/* Date: 1/1/2008                                                          */
/* Author: Andrew Que (http://www.DrQue.net/)                              */
/* Revisions:                                                              */
/*  1.0 - 1/1/2008 - QUE - Creation                                        */
/*  1.1 - 2014/05/28 - QUE - Cleanup.                                      */
/*                                                                         */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/* X/Y trend with linear regression script                                 */
/* Copyright (C) 2008,2014 Andrew Que                                      */
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
/*                         (C) Copyright 2008,2014                         */
/*                               Andrew Que                                */
/*                                   ð|>                                   */
/*=========================================================================*/
/**
 * Clip circle
 *
 * Contains a function to draw a circle clipped within boundaries
 * @package XY_Plot
 * @author Andrew Que ({@link http://www.DrQue.net/})
 * @copyright Copyright (c) 2008,2014 Andrew Que
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * Draw a circle with clipping.
 *
 * Draw a filled circle and do not allow it to extend beyond a defined set of
 * boundaries.
 * This isn't the most efficient implementation, so don't use when speed is
 * essential.
 * @param resource $image image to draw circle onto
 * @param int $centerX X-coordinate of center of circle
 * @param int $centerY Y-coordinate of center of circle
 * @param int $diameter Diameter of circle
 * @param int $color Color of circle
 * @param int $clipLeft Left boundary
 * @param int $clipTop Top boundary
 * @param int $clipRight Right boundary
 * @param int $clipBottom Bottom boundary
 */
function clippedFilledCircle
(
  $image,
  $centerX,
  $centerY,
  $diameter,
  $color,
  $clipLeft = null,
  $clipTop = null,
  $clipRight = null,
  $clipBottom = null
)
{
  $radius = $diameter / 2.0;
  $count = round( -$radius );
  while ( $count <= $radius )
  {
    $y  = $centerY + $count;
    $yy = $centerY + $count;

    $delta = ( $radius * $radius ) - ( $count * $count );

    ++$count;

    if ( $delta < 0 )
      continue;

    $delta = sqrt( $delta );

    $x  = ceil( $centerX - $delta );
    $xx = floor( $centerX + $delta );

    // Above the top margin?
    if ( ( $clipTop !== null )
      && ( $y < $clipTop ) )
        continue;

    // Below the bottm margin?
    if ( ( $clipBottom !== null )
      && ( $yy > $clipBottom ) )
        continue;

    // Beyond left margin?
    if ( ( $clipLeft !== null )
      && ( $x < $clipLeft ) )
        $x = $clipLeft;

    // Beyond right margin?
    if ( ( $clipRight !== null )
      && ( $xx > $clipRight ) )
        $xx = $clipRight;

    // Off the chat?
    if ( ( $clipLeft !== null )
      && ( $xx < $clipLeft ) )
        continue;

    if ( ( $clipRight !== null )
      && ( $x > $clipRight ) )
        continue;

    // Draw line
    imageLine( $image, $x, $y, $xx, $y, $color );
  }

}

// "I understand now, Dr. Chandra. Thank you for telling me the truth."
// HAL 9000, 2010: The Year We Make Contact

?>
