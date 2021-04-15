<?php
/*=========================================================================*/
/* Name: XY_Plot.php                                                       */
/* Uses: Creates a trend with X/Y data.                                    */
/* Date: 4/12/2007                                                         */
/* Author: Andrew Que (http://www.DrQue.net/)                              */
/* Revisions:                                                              */
/*  0.1 - 4/12/2007 - QUE - Creation.                                      */
/*  1.0 - 4/29/2007 - QUE - Base functionality.                            */
/*  1.1 - 11/24/2007 - QUE - Added linear regression.                      */
/*  1.1.1 - 12/15/2007 - QUE - Added line clipping.                        */
/*  1.1.2 - 12/24/2007 - QUE - PHPDoc documentation added.                 */
/*  1.1.3 - 12/28/2007 - QUE - Small bug fixes.                            */
/*  1.2 - 12/29/2007 - QUE - Grid lines and labels.                        */
/*  1.2.1 - 1/1/2008 - QUE - Added circle clipping.                        */
/*  1.3 - 12/24/2012 - QUE - Line thickness added.                         */
/*  1.4 - 2014/05/28 - QUE -                                               */
/*    + Light cleanup.                                                     */
/*    + Fixed warnings and notices.                                        */
/*                                                                         */
/* Website: http://xyplot.drque.net/                                       */
/*   The newest version and information about this project are on the      */
/*   website.                                                              */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/* X/Y trend with linear regression script                                 */
/* Copyright (C) 2007-2008, 2012, 2014 Andrew Que                          */
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
/*                   (C) Copyright 2007-2008, 2012, 2014                   */
/*                               Andrew Que                                */
/*                                   =|>                                   */
/*=========================================================================*/
/**
 * X/Y plot
 *
 * Used for creating graphs of X/Y data and includes functions for
 * drawing mean average and linear regression.  Functions include:
 * <ul>
 *  <li>Line drawing.  Lines are drawn between each point</li>
 *  <li>Point drawing.  Each point is charted</li>
 *  <li>Mean average.  A line denoting the average</li>
 *  <li>Linear regression.  Linear regression line</li>
 *  <li>Grid lines and labels.</li>
 * </ul>
 *
 * @package XY_Plot
 * @author Andrew Que ({@link http://www.DrQue.net/})
 * @copyright Copyright (c) 2007-2008, 2012, 2014, Andrew Que
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * Requirenments
 */
require_once( 'ClipLine.php' );
require_once('ClipCircle.php' );

/**
 * A rectangular area define by the upper left corner and the lower right.
 * @package XY_Plot
 */
class Window
{
  protected $x;
  protected $y;
  protected $xx;
  protected $yy;

  /**
   * Constructor.
   *
   * Create a new window with specified area.
   * @param $x left edge.
   * @param $y top edge.
   * @param $xx right edge.
   * @param $yy bottom edge.
   */
  public function __construct( $x, $y, $xx, $yy )
  {
    $this->x  = $x;
    $this->y  = $y;
    $this->xx = $xx;
    $this->yy = $yy;
  }

  /**
   * Resize window.
   *
   * Change the size of the window to the new area.
   * @param $x left edge.
   * @param $y top edge.
   * @param $xx right edge.
   * @param $yy bottom edge.
   */
  public function resize( $x, $y, $xx, $yy )
  {
    $this->x  = $x;
    $this->y  = $y;
    $this->xx = $xx;
    $this->yy = $yy;
  }

  /**
   * Get left coordinate.
   */
  public function getX()
  {
    return $this->x;
  }

  /**
   * Get top coordinate.
   */
  public function getY()
  {
    return $this->y;
  }

  /**
   * Get right coordinate.
   */
  public function getXX()
  {
    return $this->xx;
  }

  /**
   * Get bottom coordinate.
   */
  public function getYY()
  {
    return $this->yy;
  }

  /**
   * Return width (delta x).
   */
  public function getWidth()
  {
    return $this->xx - $this->x;
  }

  /**
   * Return height (delta y).
   */
  public function getHeigth()
  {
    return $this->yy - $this->y;
  }
}

/**
 * X/Y plot class.
 *
 * Used to create a plot of X/Y data.
 * Examples of use:
 * <code>
 *   $plot = new XY_Plot( $image ); // <- Initilize.
 *   $plot->addData( 1, 5 ); // <- Add some data.
 *   $plot->addData( 3, 2 );
 *   $plot->addData( 4, 4 );
 *   $plot->setColor( $black ); // <- Line color.
 *   $plot->renderWithLines();  // <- Draw lines into $image.
 * </code>
 * @package XY_Plot
 */
class XY_Plot
{
  protected $image;
  protected $window;
  protected $xMax                      = -1;
  protected $xMin                      = -1;
  protected $yMin                      = 0;
  protected $yMax                      = 100;
  protected $color                     = 0;
  protected $linearRegressionColor     = 0;
  protected $linearRegressionThickness = 4;
  protected $lineThickness             = 1;
  protected $linearAverageColor        = 0;
  protected $data                      = array();
  protected $windowedData              = array();
  protected $renderMethod              = "Points";
  protected $slope;
  protected $correlation;
  protected $yIntercept;
  protected $average;
  protected $maxX_Distance             = 0;
  protected $circleSize                = 5;
  protected $averageWidth              = 1;
  protected $isDataCalculated          = false;

  protected $yMajorDivisionExtension   = 0;
  protected $yMajorDivisionScale       = 1;
  protected $yMajorDivisionColor;
  protected $yMajorDivisionTextColor;
  protected $yMajorTextCallback        = null;
  protected $yMajorTextFontSize        = 2;
  protected $yMinorDivisionScale       = 1;
  protected $yMinorDivisionColor;

  protected $xMajorDivisionExtension   = 0;
  protected $xMajorDivisionScale       = 1;
  protected $xMajorDivisionColor;
  protected $xMajorDivisionTextColor;
  protected $xMajorTextCallback        = null;
  protected $xMajorTextFontSize        = 2;

  protected $xMajorCustomStart         = null;
  protected $xMajorCustomIncrement     = null;
  protected $xMajorCustomEnd           = null;

  protected $xMinorDivisionScale       = 1;
  protected $xMinorDivisionColor;
  protected $xMinorTextFontSize        = 2;

  protected $xMinorCustomStart         = null;
  protected $xMinorCustomIncrement     = null;
  protected $xMinorCustomEnd           = null;
  /**#@-*/

  /**
   * Constructor.
   * @param resource $image The image to use for the plot.
   */
  public function __construct( $image )
  {
    $this->image  = $image;

    // Assume graph uses entire window
    $this->window =
      new Window
      (
        0,
        0,
        imagesX( $this->image ),
        imagesY( $this->image )
      );
  }

  /**
   * Set the width of the mean average plot.
   *
   * Set the width of the mean average plot line.
   * @param int $value New width.
   */
  public function setAverageWidth( $value )
  {
    $this->averageWidth = $value;
  }

  /**
   * Get mean average width.
   *
   * Return mean average width.
   * @return int Mean average width.
   */
  public function getAverageWidth()
  {
    return $this->averageWidth;
  }

  /**
   * Set the max and max X-span.
   *
   * The values for X-min and X-max further narrow down the data added
   * to the plot.  This specifies a range of X values that will be on the
   * the chart.
   * @param float $xMin The lowest X-value to include in chart.
   * @param float $xMax The highest X-value to include in chart.
   */
  public function setX_Span( $xMin, $xMax )
  {
    $this->xMax = $xMax;
    $this->xMin = $xMin;
    $this->isDataCalculated = false;
  }

  /**
   * Limit Y-axis range.
   *
   * Set the range for Y values.  Values above and below the min and
   * max values will be clipped.
   * @param float $yMin The lowest Y-value to include in chart.
   * @param float $yMax The highest Y-value to include in chart.
   */
  public function setY_Span( $yMin, $yMax )
  {
    $this->yMin = $yMin;
    $this->yMax = $yMax;
  }

  /**
   * Resize the viewing window.
   *
   * @param int $x left edge.
   * @param int $y top edge.
   * @param int $xx right edge.
   * @param int $yy bottom edge.
   */
  public function sizeWindow( $x, $y, $xx, $yy )
  {
    $this->window->resize( $x, $y, $xx, $yy );
  }

  /**
   * Set the color of the line plot.
   *
   * This is the color of the line created by <b>renderWithLines()</b>.
   * @param resource $color Color of the line.
   */
  public function setColor( $color )
  {
    $this->color = $color;
  }

  /**
   * Set color of the linear regression line.
   *
   * This is the color of the line created by <b>renderLinearRegression()</b>.
   * @param resource $color Color of the line.
   */
  public function setLinearRegressionColor( $color )
  {
    $this->linearRegressionColor = $color;
  }

  /**
   * Set color of the average line.
   *
   * This is the color of the line created by <b>renderMeanPlot()</b>.
   * @param resource $color Color of the line.
   */
  public function setAverageColor( $color )
  {
    $this->linearAverageColor = $color;
  }

  /**
   * Flush all data.
   *
   * This will remove add data points from the plot.  Useful for adding more
   * then one plot to an image.
   */
  public function resetData()
  {
    $this->data = array();
    $this->isDataCalculated = false;
  }

  /**
   * Add a data point.
   *
   * This called for every data point to be part of the chart.
   * @param int $x X-cord of data point.
   * @param int $y Y value of point at X.
   */
  public function addData( $x, $y )
  {
    $this->data[ "$x" ] = (float)$y;
    $this->isDataCalculated = false;
  }

  /**
   * Max gab between x values.
   *
   * Set the maximum gap between x values before a line no longer
   * connects them.  Useful when areas of data are missing.  Set to
   * 0 to disable (default).
   * @param float $distance Max distance between x values.
   */
  public function setMaxX_Distance( $distance )
  {
    $this->maxX_Distance = $distance;
  }

  /**
   * Get the max gab between x values.
   * @return float Max distance between x values.
   */
  public function getMaxX_Distance()
  {
    return $this->maxX_Distance;
  }

  /**
   * Linear regression line thickness.
   *
   * Set thickness of linear regression line.  Default is 4 pixels.
   * @param int $value Thickness.
   */
  public function setLinearRegressionThickness( $value )
  {
    $this->linearRegressionThickness = $value;
  }

  /**
   * Get Linear regression line thickness.
   *
   * Return thickness of linear regression line.
   * @return int Thickness.
   */
  public function getLinearRegressionThickness()
  {
    return $this->linearRegressionThickness;
  }

  /**
   * Line thickness.
   *
   * Set thickness of the line.  Default is 1 pixels.
   * @param int $value Thickness.
   */
  public function setLineThickness( $value )
  {
    $this->lineThickness = $value;
  }

  /**
   * Get line thickness.
   *
   * Return thickness of line.
   * @return int Thickness.
   */
  public function getLineThickness()
  {
    return $this->lineThickness;
  }

  /**
   * Set circle size for data points.
   *
   * The circle size is a parameter for the <b>renderPoints()</b>
   * method.  The size is the diameter of the circle used to denote a point
   * on the chart.  Default size is 5 pixels.
   * @param int $size Diameter of circle.
   */
  public function setCircleSize( $size )
  {
    $this->circleSize = $size;
  }

  /**
   * Return the lowest Y data scale.
   * @return float Lowest Y data scale.
   */
  public function getY_Min()
  {
    return $this->yMin;
  }

  /**
   * Return the highest Y data scale.
   * @return float Highest Y data scale.
   */
  public function getY_Max()
  {
    return $this->yMax;
  }

  /**
   * Return the lowest X data scale.
   * @return float Lowest X data scale.
   */
  public function getX_Min()
  {
    return $this->xMin;
  }

  /**
   * Return the highest x data scale.
   * @return float Highest x data scale.
   */
  public function getX_Max()
  {
    return $this->xMax;
  }

  /**
   * Computed slope.
   *
   * Return the slope computer through linear regression.  Requires a call
   * to <b>calculateLinearRegression()</b> before containing
   * valid data.
   * This is the <i>m</i> part of the standard line function <i>y = mx + b</i>
   * @return float Slope of linear regression line.
   */
  public function getSlope()
  {
    return $this->slope;
  }

  /**
   * Computed Y-intercept.
   *
   * Returns the Y-intercept computed through linear regression.  Requires
   * a call to <b>calculateLinearRegression()</b> before containing
   * valid data.
   * This is the <i>b</i> part of the standard line function <i>y = mx + b</i>
   * @return float Y-intercept.
   */
  public function getY_Intercept()
  {
    return $this->yIntercept;
  }

  /**
   * Correlation coefficient.
   *
   * Returns the correlation coefficient computed through linear regression.  Requires
   * a call to <b>calculateLinearRegression()</b> before containing
   * valid data.
   * @return float Correlation coefficient.
   */
  public function getCorrelation()
  {
    return $this->correlation;
  }

  /**
   * Mean average.
   *
   * Return the mean average of data within X span.  Requires a call
   * to <b>calculateMeanAverage</b> before containing valid data.
   * @return float Mean average.
   */
  public function getAverage()
  {
    return $this->average;
  }

  //------------------------------------------------------------------------

  /**
   * Build an array narrowed in on the x-frame.
   */
  protected function buildWindowArray()
  {
    // Have we built this array already?
    if ( ! $this->isDataCalculated )
    {
      // Sort data.
      ksort( $this->data );

      // Clear array.
      $this->windowedData = array();

      // For all samples.
      foreach ( $this->data as $x => $y )
      {
         // Within sample window?
         if ( ( $x < $this->xMin )
           || ( $x > $this->xMax ) )
           continue;

        // Add to windowed data.
        $this->windowedData[] =
          array( "x" => $x, "y" => $y );
      }

      // Data is now calculated.
      $this->isDataCalculated = true;
    }

    return $this->windowedData;
  }

  /**
   * Scale a Y data point into the Y window scale.
   *
   * This function will translate the value in $y to a vertical
   * pixel location in the image window.  This function can be useful
   * for drawing grid lines.
   * @param float $y The Y-value to translate.
   * @return float translated Y-value.
   */
  public function scaleY( $y )
  {
    // Compute delta Y.
    $yDelta = $this->yMax - $this->yMin;

    // Strip minimum value.
    $y -= $this->yMin;

    // Scale data.
    if ( $yDelta != 0 )
      $y = ( $y * $this->window->getHeigth() ) / $yDelta;
    else
      $y = 0;

    // Adjust for window height.
    $y = $this->window->getHeigth() - $y;

    return $y;
  }

  /**
   * Scale a X data point into the X window scale.
   *
   * This function will translate the value in $x to a horizontal.
   * pixel location in the image window.
   * @since version 1.2.
   * @param float $x The X-value to translate.
   * @return float Translated X-value.
   */
  public function scaleX( $x )
  {
    // Scale x
    $x -= $this->xMin;
    $x  = $x * $this->window->getWidth();
    $x /= $this->xMax - $this->xMin;
    $x  = round( $x );

    return $x;
  }

  /**
   * Calculate the lowest value of Y data.
   *
   * @return float Lowest Y value in data.
   */
  public function findY_Min()
  {
    $windowArray = $this->buildWindowArray();
    $min = $windowArray[ 0 ][ "y" ];

    // For all data
    for ( $index = 0; $index < count( $windowArray ); ++$index )
      $min = Min( $min, $windowArray[ $index ][ "y" ] );

    return $min;
  }

  /**
   * Calculate the highest value of Y data.
   *
   * @return float Highest Y value in data.
   */
  public function findY_Max()
  {
    $windowArray = $this->buildWindowArray();
    $max = $windowArray[ 0 ][ "y" ];

    // For all data.
    for ( $index = 0; $index < count( $windowArray ); ++$index )
      $max = Max( $max, $windowArray[ $index ][ "y" ] );

    return $max;
  }

  /**
   * Number of data points.
   *
   * Find the number of data points in the window.
   * @return int Number of data points.
   */
  public function findNumberOfDataPoints()
  {
    $windowArray = $this->buildWindowArray();
    return count( $windowArray );
  }

  /**
   * Calculate the lowest value of X data.
   *
   * @return float Lowest X value in data.
   */
  public function findX_Min()
  {
    $min = min( array_keys( $this->data ) );

    return $min;
  }

  /**
   * Calculate the highest value of X data.
   * @return float Highest X value in data.
   */
  public function findX_Max()
  {
    $max = max( array_keys( $this->data ) );

    return $max;
  }

  /**
   * Automaticlly adjust the min and max values.
   *
   * Scale Y min/max to fit all the data.  This will find the min and
   * max values of Y and set Y-min and Y-max accordingly.  A rounding
   * parameters can be used to round down/up and min/max values.  For
   * example, a round value of 0.1 will round to the nearest 10th.  If
   * Y-max was .22 and Y-min was .15, the values used with be .3 and .1
   * @param int $round Decimal to round to.
   */
  public function autoScaleY_MinMax( $round )
  {
    // Fetch data.
    $windowArray = $this->buildWindowArray();
    $first = current( $windowArray );
    $yMax = $yMin = $first[ "y" ];

    // For all data points.
    foreach ( $windowArray as $data )
    {
      $yMin = Min( $yMin, $data[ "y" ] );
      $yMax = Max( $yMax, $data[ "y" ] );
    }

    // If rounding.
    if ( $round > 0 )
    {
      $round = 1 / $round;
      $yMin = floor( $yMin * $round ) / $round;
      $yMax = ceil( $yMax * $round ) / $round;
    }

    // Set this as the new span.
    $this->setY_Span( $yMin, $yMax );
  }

  /**
   * Automaticlly adjust the Y min and max values.
   *
   * @deprecated Use autoScaleY_MinMax.
   */
  public function adjustMinMax( $round )
  {
    $this->autoScaleY_MinMax( $round );
  }

  /**
   * Automaticlly adjust the X min and max values
   *
   * Scale X min/max to fit all the data.  This will find the min and
   * max values of X and set X-min and X-max accordingly.  A rounding
   * parameters can be used to round down/up and min/max values.  For
   * example, a round value of 0.1 will round to the nearest 10th.  If
   * X-max was .22 and X-min was .15, the values used with be .3 and .1
   * @param int $round Decimal to round to.
   */
  public function autoScaleX_MinMax( $round = 0 )
  {
    $xMin = min( array_keys( $this->data ) );
    $xMax = max( array_keys( $this->data ) );

    // If rounding
    if ( $round > 0 )
    {
      $round = 1 / $round;
      $xMin = floor( $xMin * $round ) / $round;
      $xMax = ceil( $xMax * $round ) / $round;
    }

    // Set this as the new span
    $this->setX_Span( $xMin, $xMax );
  }


  /**
   * Calculate mean average.
   *
   * Calculate the mean average of data inside the plot.
   * @return float Average.
   */
  public function calculateMeanAverage()
  {
    // Fetch data
    $windowArray = $this->buildWindowArray();
    $count = $n = count( $windowArray );

    // Do all summations
    $average = 0.0;
    for ( $index = 0; $index < $count; ++$index )
      $average += $windowArray[ $index ][ "y" ];

    $average /= $count;

    $this->average = $average;
  }

  /**
   * Calculate slope and y-intercept of least square linear regression.
   *
   * This function is needed if the data from the linear regression
   * calculation is needed but the linear regression plot hasn't been drawn.
   * After this function is called, <b>getSlope</b> and <b>getY_Intercept</b>
   * will return valid information.
   */
  public function calculateLinearRegression()
  {
    $deltaX = $this->xMax - $this->xMin;

    // Fetch data.
    $windowArray = $this->buildWindowArray();
    $count = $n = count( $windowArray );

    // No data?
    if ( $count == 0 )
    {
      $this->slope = $m = 0;
      $this->yIntercept = $b = 0;
      return;
    }

    // Do all summations.
    $sumX = $sumY = $sumXY = $sumXX = $sumYY = 0.0;
    for ( $index = 0; $index < $count; ++$index )
    {
      $x = $windowArray[ $index ][ "x" ] - $this->xMin;
      $y = $windowArray[ $index ][ "y" ];
      $sumX  += $x;       // ä x
      $sumY  += $y;       // ä y
      $sumXY += $x * $y;  // ä xy
      $sumXX += $x * $x;  // ä x^2
      $sumYY += $y * $y;  // ä y^2
    }

    $sumX_SumX = $sumX * $sumX; // ( äx )^2
    $sumY_SumY = $sumY * $sumY; // ( äy )^2

    // Slope:
    //          n äxy - äx äy
    //   m = -------------------
    //        n äx^2 - ( äx )^2
    //
    $numerator  = ( $n * $sumXY ) - ( $sumX * $sumY );
    $denominator = ( $n * $sumXX ) - $sumX_SumX;

    if ( $denominator )
      $m = $numerator / $denominator;
    else
      $m = 0;

    // Y-intercept:
    //        äy - m äx
    //   b = -----------
    //            n
    //
    $b  = ( $sumY - ( $m * $sumX ) ) / $n;

    // Correlation coefficient
    //                     n äxy - äx äy
    //   r = ---------------------------------------------
    //        û[ n äx^2 - ( äx )^2 ][ n äy^2 - ( äy )^2 ]
    //
    $numerator = ( $n * $sumXY ) - ( $sumX * $sumY );

    $accumulatorX = ( $n * $sumXX ) - $sumX_SumX;
    $accumulatorY = ( $n * $sumYY ) - $sumY_SumY;
    $denominator  = sqrt( $accumulatorX * $accumulatorY );

    if ( $denominator )
      $r = $numerator / $denominator;
    else
      $r = 0;

    // Save information
    $this->slope = $m;
    $this->yIntercept = $b;
    $this->correlation = $r;
  }

  //------------------------------------------------------------------------

  /**
   * Draw points for each data point given.
   *
   * This creates a point plot in the image specified during creation of
   * the class.  Each point is of the size specified by <b>setCircleSize</b>
   * and of the color specified by <b>setColor</b>.
   */
  public function renderPoints()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    $height = $this->window->getHeigth();

    // Delta Y.
    $yDelta = $this->yMax - $this->yMin;

    // Delta X.
    $deltaX = $this->xMax - $this->xMin;

    // Get data points.
    $windowArray = $this->buildWindowArray();

    // Number of data points.
    $indexMax  = count( $windowArray ) ;

    // For each point.
    for ( $index = 0; $index < $indexMax; ++$index )
    {
      // Scale x.
      $x = $this->scaleX( $windowArray[ $index ][ "x" ] );

      // Scale y.
      $y  = $this->scaleY( $windowArray[ $index ][ "y" ] );

      // Draw point.
      clippedFilledCircle
      (
        $this->image,
        $marginLeft + $x,
        $marginTop + $y,
        $this->circleSize,
        $this->color,
        $marginLeft,
        $marginTop,
        $marginRight,
        $marginBottom
      );

    }

  }

  /**
   * Draw lines from point to point.
   *
   * This creates a line plot in the image specified during creation of
   * the class.  Lines will connect all the points in the graph, sorted by
   * their x-values.  A gap in the chart will be left if the distance
   * between two points is over the values specified with <b>setMaxX_Distance</b>.
   * The color of the lines is specified with <b>setColor</b>.
   */
  public function renderWithLines()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Delta Y.
    $yDelta = $this->yMax - $this->yMin;

    // Delta X.
    $deltaX = $this->xMax - $this->xMin;

    // Get data points.
    $windowArray = $this->buildWindowArray();

    // Number of data points.
    $indexMax = count( $windowArray ) ;

    $lastY = null;
    $lastX = 0;
    $lastRawX = null;

    // For each point.
    for ( $index = 0; $index < $indexMax; ++$index )
    {
      // Scale x.
      $x = $this->scaleX( $windowArray[ $index ][ "x" ] );

      // Scale y.
      $y  = $this->scaleY( $windowArray[ $index ][ "y" ] );

      // Delta X between this sample and last.
      $xSpan = $windowArray[ $index ][ "x" ] - $lastRawX;

      // Is this distance too great?
      if ( ( $lastY === null )
        || ( ( $this->maxX_Distance != 0 )
          && ( $xSpan > $this->maxX_Distance ) ) )
      {
        $lastX = $x;
        $lastY = $y;
        $lastRawX = $windowArray[ $index ][ "x" ];

        // Skip plotting this line
        continue;
      }

      // Locations of line.
      $pointX  = $marginLeft + $lastX;
      $pointY  = $marginTop + $lastY;
      $pointXX = $marginLeft + $x;
      $pointYY = $marginTop + $y;

      // Draw clipped line.
      drawClippedLine
      (
        $this->image,
        $pointX, $pointY,
        $pointXX, $pointYY,
        $marginLeft, $marginTop,
        $marginRight, $marginBottom,
        $this->color,
        $this->lineThickness
      );

      // Set last coordinates to current
      $lastX = $x;
      $lastY = $y;
      $lastRawX = $windowArray[ $index ][ "x" ];
    }

  }

  /**
   * Draw linear regression plot.
   *
   * This function will compute and draw a line representing the mean
   * square linear regression of the data within the window.
   */
  public function renderLinearRegression()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Calculate linear regression data.
    $this->calculateLinearRegression();

    $deltaX = $this->xMax - $this->xMin;
    $m = $this->slope;
    $b = $this->yIntercept;

    // Standard line function is y = mx + b.
    $y  = $this->scaleY( $b, false );
    $yy = $this->scaleY( ( $m * $deltaX ) + $b, false );

    $x  = $marginLeft;
    $y  = $marginTop + $y;
    $xx = $marginRight;
    $yy = $marginTop + $yy;

    // Draw line.
    drawClippedLine
    (
       $this->image,
       $x, $y,
       $xx, $yy,
       $marginLeft, $marginTop,
       $marginRight, $marginBottom,
       $this->linearRegressionColor,
       $this->linearRegressionThickness
    );
  }

  /**
   * Draw mean average line.
   *
   * This creates a line denoting the mean average in the image specified
   * during creation of the class.
   */
  public function renderMeanPlot()
  {
    // Margins.
    $marginLeft  = $this->window->getX();
    $marginTop   = $this->window->getY();
    $marginRight = $this->window->getXX();

    // Calculate average.
    $this->calculateMeanAverage();

    // Standard line function is y = mx + b.
    $y  = $this->scaleY( $this->average );

    // Draw average.
    for ( $index = 0; $index < $this->averageWidth; ++$index )
      imageLine
      (
        $this->image,
        $marginLeft,
        $marginTop + $y + $index,
        $marginRight,
        $marginTop + $y + $index,
        $this->linearAverageColor
      );
  }

  //------------------------------------------------------------------------
  // Grids
  //------------------------------------------------------------------------

  /**
   * Font size for vertical major divisions.
   *
   * This sets the size of the font (PHP's internal fonts for GD) used
   * for vertical labels.
   * @param int $value new font size must be between 1 and 5.
   * @since version 1.2.
   */
  function setY_MajorTextFontSize( $value )
  {
    assert( $value >= 1 );
    assert( $value <= 5 );
    $this->yMajorTextFontSize = $value;
  }

  /**
   * Get font size for vertical divisions.
   *
   * Return the font size for vertical division.
   * @return int font size for vertical divisions.
   * @since version 1.2
   */
  function getY_MajorTextFontSize()
  {
    return $this->yMajorTextFontSize;
  }

  /**
   * Major vertical division extension.
   *
   * Number of pixels past the margin to extend major vertical divisions
   * Default is 0.
   * @param int $value Pixel to extend.
   * @since version 1.2
   */
  function setY_MajorDivisionExtension( $value )
  {
    $this->yMajorDivisionExtension = $value;
  }

  /**
   * Major vertical division extension.
   *
   * Current number of pixels past the margin to extend major vertical
   * divisions.
   * @return int Vertical division extension.
   * @since version 1.2
   */
  function getY_MajorDivisionExtension()
  {
    return $this->yMajorDivisionExtension;
  }

  /**
   * Major vertical scale.
   *
   * These are the increments in which the major vertical scale
   * is labeled.  For example, a value of 50 would place divides every
   * 50 units.
   * @param float $value Scale.
   * @since version 1.2.
   */
  function setY_MajorDivisionScale( $value )
  {
    $this->yMajorDivisionScale = $value;
  }

  /**
   * Get major vertical scale.
   *
   * Return increments in which the major vertical scale
   * is labeled.
   * @return float Increments of scale.
   * @since version 1.2
   */
  function getY_MajorDivisionScale()
  {
    return $this->yMajorDivisionScale;
  }

  /**
   * Color of major vertical divisions.
   *
   * Set the color of major vertical divisions.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setY_MajorDivisionColor( $value )
  {
    $this->yMajorDivisionColor = $value;
  }

  /**
   * Get color of major vertical divisions.
   *
   * Return the color of major vertical divisions.
   * @return resource Color.
   * @since version 1.2.
   */
  function getY_MajorDivisionColor()
  {
    return $this->yMajorDivisionColor;
  }

  /**
   * Color of major vertical division labels.
   *
   * Set color of major vertical divisions labels.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setY_MajorDivisionTextColor( $value )
  {
    $this->yMajorDivisionTextColor = $value;
  }

  /**
   * Get color of major vertical division labels.
   *
   * Return the current color of text labels for vertical division labels.
   * @return resource Color.
   * @since version 1.2.
   */
  function getY_MajorDivisionTextColor()
  {
    return $this->yMajorDivisionTextColor;
  }

  /**
   * Text callback for vertical division labels.
   *
   * Assigns a custom callback for creating a string for vertical
   * labels.  The callback function is passed 3 parameters: a value,
   * x location and y location.  The callback function can do one of two
   * things.
   * The callback function may return a string with the value properly
   * formatted.
   * <code>
   *   function formatCallback( $value )
   *   {
   *     return "$" . number_format( $value, 2 );
   *   }
   * </code>
   * This function is an example of a simple callback, formatting the
   * number as a dollar amount.
   * The other option is to make place custom text at the location
   * <code>
   *   function formatCallback( $value, $x, $y )
   *   {
   *     // Price for this division
   *     imageString(
   *       $image,
   *       $fontSize,
   *       $x + $centerBias, $y,
   *       $string,
   *       $colorMap[ "Black" ] );
   *
   *     return "";
   *   }
   * </code>
   *  This example (which is not functional) shows how such a function might
   * look.  The parameters of $x and $y are the location of the point at
   * which the text is to be placed.  By returning nothing, XY Plot will
   * print nothing to the chart.
   * @param callable $value callback function.
   * @since version 1.2.
   */
  function setY_MajorTextCallback( $value )
  {
    $this->yMajorTextCallback = $value;
  }

  /**
   * Get text callback for vertical division labels.
   *
   * Return the callback function for vertical division labels.
   * @return callable Function name.
   * @since version 1.2.
   */
  function getY_MajorTextCallback()
  {
    return $this->yMajorTextCallback;
  }

  /**
   * Major vertical divisions.
   *
   * Draw major vertical (top to bottom) divisions and add text labels.
   * @since version 1.2.
   */
  function drawY_MajorDivisions()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Round off start and end of horizontal divisions.
    $round = 1.0 / $this->yMajorDivisionScale;
    $yMinStart = floor( $this->yMin * $round ) / $round;
    $yMaxStart = ceil( $this->yMax * $round ) / $round;
    $yPosition = $yMaxStart;

    // While more divisions remain to be drawn.
    while ( $yPosition >= $yMinStart )
    {
      // Get vertical location of line.
      $y  = round( $this->scaleY( $yPosition, false ) );
      $y += $marginTop;

      // Is this within the margins?
      if ( ( $y < $marginTop )
        || ( $y > $marginBottom ) )
      {
        $yPosition -= $this->yMajorDivisionScale;
        continue;
      }

      $x = $marginRight + $this->yMajorDivisionExtension;

      // Division line.
      imageLine
      (
        $this->image,
        $marginLeft,
        $y,
        $x,
        $y,
        $this->yMajorDivisionColor
      );

      $callback = $this->yMajorTextCallback;

      // Lable for division.
      if ( $callback )
        $string = $callback( $yPosition, $x, $y );
      else
        $string = number_format( $yPosition, 0 );

      // If there is a string to print.
      if ( $string != "" )
      {
        $centerBias  = imageFontHeight( $this->yMajorTextFontSize );
        $centerBias /= 2;
        $centerBias  = -round( $centerBias );

        // Price for this division.
        imageString
        (
          $this->image,
          $this->yMajorTextFontSize,
          $x + 4, $y + $centerBias,
          $string,
          $this->yMajorDivisionTextColor
        );
      }

      // Next...
      $yPosition -= $this->yMajorDivisionScale;
    }

  }

  /**
   * Minor vertical scale.
   *
   * These are the increments in which the minor vertical scale
   * is labeled.  For example, a value of 50 would place divides every
   * 50 units.
   * @param float $value Scale.
   * @since version 1.2.
   */
  function setY_MinorDivisionScale( $value )
  {
    $this->yMinorDivisionScale = $value;
  }

  /**
   * Get minor vertical scale.
   *
   * Return increments in which the minor vertical scale
   * is labeled.
   * @return float Increments of scale.
   * @since version 1.2.
   */
  function getY_MinorDivisionScale()
  {
    return $this->yMinorDivisionScale;
  }

  /**
   * Color of minor vertical divisions.
   *
   * Set the color of minor vertical divisions.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setY_MinorDivisionColor( $value )
  {
    $this->yMinorDivisionColor = $value;
  }

  /**
   * Get color of minor vertical divisions.
   *
   * Return the color of minor vertical divisions.
   * @return resource Color.
   * @since version 1.2.
   */
  function getY_MinorDivisionColor()
  {
    return $this->yMinorDivisionColor;
  }

  /**
   * Minor vertical divisions.
   *
   * Draw minor vertical (top to bottom) divisions.
   * @since version 1.2.
   */
  function drawY_MinorDivisions()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Round off start and end of horizontal divisions.
    $round = 1.0 / $this->yMinorDivisionScale;
    $yMinStart = floor( $this->yMin * $round ) / $round;
    $yMaxStart = ceil( $this->yMax * $round ) / $round;
    $yPosition = $yMaxStart;

    // While more divisions remain to be drawn.
    while ( $yPosition >= $yMinStart )
    {
      // Get vertical location of line.
      $y  = round( $this->scaleY( $yPosition, false ) );
      $y += $marginTop;

      // Is this within the margins?
      if ( ( $y < $marginTop )
        || ( $y > $marginBottom ) )
      {
        $yPosition -= $this->yMinorDivisionScale;
        continue;
      }

      $x = $marginRight;

      // Division line.
      imageLine
      (
        $this->image,
        $marginLeft,
        $y,
        $x,
        $y,
        $this->yMinorDivisionColor
      );

      // Next...
      $yPosition -= $this->yMinorDivisionScale;
    }

  }

  /**
   * Custom major horizontal start value.
   *
   * This is the value from which to start the grid lines.
   * Set to null to disable (default).
   * @param float $value Value to start from.
   * @since version 1.2.
   */
  function setX_MajorCustomStart( $value )
  {
    $this->xMajorCustomStart = $value;
  }

  /**
   * Return custom start value.
   * @return float Custom start value.
   * @since version 1.2.
   */
  function getX_MajorCustomStart()
  {
    return $this->xMajorCustomStart;
  }

  /**
   * Custom major horizontal division increment function.
   *
   * The custom increment function must take a value and return
   * the next value.  This function is useful if the graph is not
   * split up linearly, such as with logarithmic divisions or divisions
   * by month.
   * @param callable $value A custom increment function.
   * @since version 1.2.
   */
  function setX_MajorCustomIncrement( $value )
  {
    $this->xMajorCustomIncrement = $value;
  }

  /**
   * Get custom major horizontal division increment function.
   *
   * @return callable Major horizontal division increment function.
   * @since version 1.2.
   */
  function getX_MajorCustomIncrement()
  {
    return $this->xMajorCustomIncrement;
  }

  /**
   * Custom major horizontal division end value.
   *
   * A value at which to stop making division marks.  This can be useful
   * if the division lines stop before the end of the chat.
   * @param float $value X value at which to stop.
   * @since version 1.2.
   */
  function setX_MajorCustomEnd( $value )
  {
    $this->xMajorCustomEnd = $value;
  }

  /**
   * Get custom major horizontal division end value.
   * @return float Custom major horizontal division end value.
   * @since version 1.2.
   */
  function getX_MajorCustomEnd()
  {
    return $this->xMajorCustomEnd;
  }

  /**
   * Font size for horizontal major divisions.
   *
   * This sets the size of the font (PHP's internal fonts for GD) used
   * for horizontal labels.
   * @param int $value New font size (must be between 1 and 5).
   * @since version 1.2.
   */
  function setX_MajorTextFontSize( $value )
  {
    assert( $value >= 1 );
    assert( $value <= 5 );
    $this->xMajorTextFontSize = $value;
  }

  /**
   * Get font size for horizontal divisions.
   *
   * Return the font size for horizontal division.
   * @return int Font size for horizontal divisions.
   * @since version 1.2.
   */
  function getX_MajorTextFontSize()
  {
    return $this->xMajorTextFontSize;
  }

  /**
   * Major horizontal division extension.
   *
   * Number of pixels past the margin to extend major horizontal divisions
   * Default is 0.
   * @param int $value Pixel to extend.
   * @since version 1.2.
   */
  function setX_MajorDivisionExtension( $value )
  {
    $this->xMajorDivisionExtension = $value;
  }

  /**
   * Major horizontal division extension.
   *
   * Current number of pixels past the margin to extend major horizontal
   * divisions.
   * @return int Horizontal division extension.
   * @since version 1.2.
   */
  function getX_MajorDivisionExtension()
  {
    return $this->xMajorDivisionExtension;
  }

  /**
   * Major horizontal scale.
   *
   * These are the increments in which the major horizontal scale
   * is labeled.  For example, a value of 50 would place divides every
   * 50 units.
   * @param float $value Scale.
   * @since version 1.2.
   */
  function setX_MajorDivisionScale( $value )
  {
    $this->xMajorDivisionScale = $value;
  }

  /**
   * Get major horizontal scale.
   *
   * Return increments in which the major horizontal scale
   * is labeled.
   * @return float Increments of scale.
   * @since version 1.2.
   */
  function getX_MajorDivisionScale()
  {
    return $this->xMajorDivisionScale;
  }

  /**
   * Color of horizontal divisions.
   *
   * Set the color of major horizontal divisions.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setX_MajorDivisionColor( $value )
  {
    $this->xMajorDivisionColor = $value;
  }

  /**
   * Get color of major horizontal divisions.
   *
   * Return the color of major horizontal divisions.
   * @return resource Color.
   * @since version 1.2.
   */
  function getX_MajorDivisionColor()
  {
    return $this->xMajorDivisionColor;
  }

  /**
   * Color of major horizontal division labels.
   *
   * Set color of major horizontal divisions labels.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setX_MajorDivisionTextColor( $value )
  {
    $this->xMajorDivisionTextColor = $value;
  }

  /**
   * Get color of major horizontal division labels.
   *
   * Return the current color of text labels for horizontal division labels.
   * @return resource Color.
   * @since version 1.2.
   */
  function getX_MajorDivisionTextColor()
  {
    return $this->xMajorDivisionTextColor;
  }

  /**
   * Text callback for horizontal division labels.
   *
   * Assigns a custom callback for creating a string for vertical
   * labels.  The callback function is passed 3 parameters: a value,
   * x location and y location.  The callback function can do one of two
   * things.
   * The callback function may return a string with the value properly
   * formatted.
   * <code>
   *   function formatCallback( $value )
   *   {
   *     return "$" . number_format( $value, 2 );
   *   }
   * </code>
   * This function is an example of a simple callback, formatting the
   * number as a dollar amount.
   * The other option is to make place custom text at the location
   * <code>
   *   function formatCallback( $value, $x, $y )
   *   {
   *     // Price for this division
   *     imageString(
   *       $image,
   *       $fontSize,
   *       $x + $centerBias, $y,
   *       $string,
   *       $colorMap[ "Black" ] );
   *
   *     return "";
   *   }
   * </code>
   *  This example (which is not functional) shows how such a function might
   * look.  The parameters of $x and $y are the location of the point at
   * which the text is to be placed.  By returning nothing, XY Plot will
   * print nothing to the chart.
   * @param callable $value callback function.
   * @since version 1.2.
   */
  function setX_MajorTextCallback( $value )
  {
    $this->xMajorTextCallback = $value;
  }

  /**
   * Get text callback for horizontal division labels.
   *
   * Return the callback function for horizontal division labels.
   * @return callable Function name.
   * @since version 1.2.
   */
  function getX_MajorTextCallback()
  {
    return $this->xMajorTextCallback;
  }

  /**
   * Major horizontal divisions.
   *
   * Draw major horizontal (left to right) divisions and add text labels.
   * @since version 1.2.
   */
  function drawX_MajorDivisions()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Round off start and end of horizontal divisions.
    $round = 1.0 / $this->xMajorDivisionScale;

    // Start.
    if ( $this->xMajorCustomStart === null )
      $xPosition = $xMinStart = floor( $this->xMin * $round ) / $round;
    else
      $xPosition = $this->xMajorCustomStart;

    // End.
    if ( $this->xMajorCustomEnd === null )
      $xMaxStart = ceil( $this->xMax * $round ) / $round;
    else
      $xMaxStart = $this->xMajorCustomEnd;

    $incFunction = $this->xMajorCustomIncrement;

    // While more divisions remain to be drawn.
    while ( $xPosition <= $xMaxStart )
    {
      // Get vertical location of line.
      $x  = round( $this->scaleX( $xPosition, false ) );
      $x += $marginLeft;

      // Is this within the margins?
      if ( ( $x < $marginLeft )
        || ( $x > $marginRight ) )
      {
        if ( $incFunction === null )
          $xPosition += $this->xMajorDivisionScale;
        else
          $xPosition = $incFunction( $xPosition );

        continue;
      }

      $y = $marginBottom + $this->xMajorDivisionExtension;

      // Division line
      imageLine
      (
        $this->image,
        $x,
        $marginTop,
        $x,
        $y,
        $this->xMajorDivisionColor
      );

      $callback = $this->xMajorTextCallback;

      // Lable for division.
      if ( $callback )
        $string = $callback( $xPosition, $x, $y );
      else
        $string = number_format( $xPosition, 0 );

      if ( $string != "" )
      {
        // Center the text.
        $centerBias  = strlen( $string );
        $centerBias *= imageFontWidth( $this->xMajorTextFontSize );
        $centerBias /= 2;
        $centerBias  = -round( $centerBias );

        // Price for this division.
        imageString(
          $this->image,
          $this->xMajorTextFontSize,
          $x + $centerBias, $y,
          $string,
          $this->xMajorDivisionTextColor );
      }

      // Next...
      if ( $incFunction === null )
        $xPosition += $this->xMajorDivisionScale;
      else
        $xPosition = $incFunction( $xPosition );
    }

  }

  /**
   * Custom Minor horizontal start value.
   *
   * This is the value from which to start the grid lines.
   * Set to null to disable (default).
   * @param float $value Value to start from.
   * @since version 1.2.
   */
  function setX_MinorCustomStart( $value )
  {
    $this->xMinorCustomStart = $value;
  }

  /**
   * Return custom start value.
   * @return float Custom start value.
   * @since version 1.2.
   */
  function getX_MinorCustomStart()
  {
    return $this->xMinorCustomStart;
  }

  /**
   * Custom Minor horizontal division increment function.
   *
   * The custom increment function must take a value and return
   * the next value.  This function is useful if the graph is not
   * split up linearly, such as with logarithmic divisions or divisions
   * by month.
   * @param callable $value A custom increment function.
   * @since version 1.2.
   */
  function setX_MinorCustomIncrement( $value )
  {
    $this->xMinorCustomIncrement = $value;
  }

  /**
   * Get custom Minor horizontal division increment function.
   *
   * @return callable Minor horizontal division increment function.
   * @since version 1.2.
   */
  function getX_MinorCustomIncrement()
  {
    return $this->xMinorCustomIncrement;
  }

  /**
   * Custom Minor horizontal division end value.
   *
   * A value at which to stop making division marks.  This can be useful
   * if the division lines stop before the end of the chat
   * @param callable $value X value at which to stop.
   * @since version 1.2.
   */
  function setX_MinorCustomEnd( $value )
  {
    $this->xMinorCustomEnd = $value;
  }

  /**
   * Get custom Minor horizontal division end value
   * @return callable Custom Minor horizontal division end value
   * @since version 1.2
   */
  function getX_MinorCustomEnd()
  {
    return $this->xMinorCustomEnd;
  }

  /**
   * Minor horizontal scale.
   *
   * These are the increments in which the minor horizontal scale
   * is labeled.  For example, a value of 50 would place divides every
   * 50 units.
   * @param float $value Scale.
   * @since version 1.2.
   */
  function setX_MinorDivisionScale( $value )
  {
    $this->xMinorDivisionScale = $value;
  }

  /**
   * Get minor horizontal scale.
   *
   * Return increments in which the minor horizontal scale
   * is labeled.
   * @return float Increments of scale.
   * @since version 1.2.
   */
  function getX_MinorDivisionScale()
  {
    return $this->xMinorDivisionScale;
  }

  /**
   * Color of horizontal divisions.
   *
   * Set the color of minor horizontal divisions.
   * @param resource $value Allocated color in the image.
   * @since version 1.2.
   */
  function setX_MinorDivisionColor( $value )
  {
    $this->xMinorDivisionColor = $value;
  }

  /**
   * Get color of minor horizontal divisions.
   *
   * Return the color of minor horizontal divisions.
   * @return resource Color.
   * @since version 1.2.
   */
  function getX_MinorDivisionColor()
  {
    return $this->xMinorDivisionColor;
  }

  /**
   * Minor horizontal divisions.
   *
   * Draw minor horizontal (left to right) divisions.
   * @since version 1.2.
   */
  function drawX_MinorDivisions()
  {
    // Margins.
    $marginLeft   = $this->window->getX();
    $marginTop    = $this->window->getY();
    $marginRight  = $this->window->getXX();
    $marginBottom = $this->window->getYY();

    // Round off start and end of horizontal divisions.
    $round = 1.0 / $this->xMinorDivisionScale;

    // Start.
    if ( $this->xMinorCustomStart === null )
      $xPosition = $xMinStart = floor( $this->xMin * $round ) / $round;
    else
      $xPosition = $this->xMinorCustomStart;

    // End.
    if ( $this->xMinorCustomEnd === null )
      $xMaxStart = ceil( $this->xMax * $round ) / $round;
    else
      $xMaxStart = $this->xMinorCustomEnd;

    $incFunction = $this->xMinorCustomIncrement;

    // While more divisions remain to be drawn.
    while ( $xPosition <= $xMaxStart )
    {
      // Get vertical location of line.
      $x  = round( $this->scaleX( $xPosition, false ) );
      $x += $marginLeft;

      // Is this within the margins?
      if ( ( $x < $marginLeft )
        || ( $x > $marginRight ) )
      {
        if ( $incFunction === null )
          $xPosition += $this->xMinorDivisionScale;
        else
          $xPosition = $incFunction( $xPosition );

        continue;
      }

      $y = $marginBottom;

      // Division line.
      imageLine
      (
        $this->image,
        $x,
        $marginTop,
        $x,
        $y,
        $this->xMinorDivisionColor
      );

      // Next...
      if ( $incFunction === null )
        $xPosition += $this->xMinorDivisionScale;
      else
        $xPosition = $incFunction( $xPosition );
    }

  }
}

// Hardware: the parts of a computer that can be kicked.  ~Jeff Pesis

?>