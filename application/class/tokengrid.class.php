<?php

/*********************************************************************
 *
 * tokengrid, a strong authentication token grid PHP class
 *
 * The tokengrid class is a Strong Authentication Token Grid solution
 * to secure the access to sensitive data through the Internet.
 * It is a good protection against Sniffing and Phishing processes.
 *
 * This kind of token cards are also used by some Swiss Banks for
 * authentication purpose.
 *
 * This class provides everything to implement a complete solution,
 * including ready to print token grids in XHTML format.
 *
 * Array-based intermediate output can be used to produce
 * PDF token grids using for example TCPDF (www.tcpdf.org).
 *
 * Any feedback is welcome !
 *
 *
 * LICENCE
 *
 *   Copyright (c) 2008, SysCo systemes de communication sa
 *   SysCo (tm) is a trademark of SysCo systemes de communication sa
 *   (http://www.sysco.ch/)
 *   All rights reserved.
 * 
 *   This file is part of the tokengrid class
 *
 *   The tokengrid class is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public License as
 *   published by the Free Software Foundation, either version 3 of the License,
 *   or (at your option) any later version.
 * 
 *   The tokengrid class is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 * 
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with the tokengrid class
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author: SysCo/al
 * @since CreationDate: 2008-04-01
 * @copyright (c) 2008 by SysCo systemes de communication sa
 * @version $LastChangedRevision: 1.2 $
 * @version $LastChangedDate: 2008-04-18 $
 * @version $LastChangedBy: SysCo/al $
 * @link $HeadURL: tokengrid.class.php $
 * @link http://developer.sysco.ch/php/
 * @link developer@sysco.ch
 * Language: PHP 4.x or higher
 *
 *
 * Usage
 *
 *   require_once('tokengrid.class.php');
 *   $token_grid = new TokenGrid([$x_grid_size = 10[, $y_grid_size = 10[, $token_length = 4[, $grid_salt = 'T@kenGr!D']]]])
 *
 *
 * Examples (see the tokengrid.demo.php file for a working example)
 *
 *   Example 1 (create a grid)
 *     <?php
 *         require_once('tokengrid.class.php');
 *         $token_grid = new TokenGrid(10, 10, 4, "MySalt");
 *         echo $token_grid->GetXhtmlGrid("SpecificUserId");
 *     ?>
 *
 *
 *   Example 2 (ask for a token)
 *     <?php
 *         require_once('tokengrid.class.php');
 *         $token_grid = new TokenGrid(10, 10, 4, "MySalt");
 *         $random_position = $token_grid->GetRandomGridPosition("SpecificUserId");
 *         echo "Please enter the token at the position ".$random_position;
 *     ?>
 *
 *
 *   Example 3 (check a token)
 *     <?php
 *         require_once('tokengrid.class.php');
 *         $token_grid = new TokenGrid(10, 10, 4, "MySalt");
 *         if ($token_grid->CheckToken($_POST['random_position'], 'SpecificUserId', $_POST['token']))
 *         {
 *             echo "Token is valid";
 *         }
 *     ?>
 *
 *
 * Change Log
 *
 *   2008-04-23 1.3   SysCo/al Code unification, comments updates, external PDF class integration
 *   2008-04-18 1.2   SysCo/al Additional methods, first public release
 *   2008-04-18 1.1   SysCo/al Cleaner code, complete sample code
 *   2008-04-01 1.0   SysCo/al Initial release
 *
 *********************************************************************/


/*********************************************************************
 *
 * TokenGrid
 * Strong Authentication Token Grid
 *
 * Creation 2008-04-01
 * @package tokengrid
 * @version v.1.0
 * @author SysCo/al
 *
 *********************************************************************/
class TokenGrid
{
    var $_x_grid_size;            // Horizontal grid size
    var $_y_grid_size;            // Vertical grid size
    var $_token_length;           // Token length
    var $_grid_salt;              // Specific grid salt for the application
    var $_horizontal_grid_labels; // Horizontal grid labels
    var $_token_chars;            // Allowed characters to compose the token
    var $_avoid_same_chars;       // Avoid same chars in a token
    var $_case_not_sensitive;     // The token input is not case sensitive
    

    /*********************************************************************
     *
     * Name: TokenGrid
     * short description: TokenGrid class constructor
     *
     * Creation 2008-04-01
     * @version v.1.0
     * @author SysCo/al
     * @param integer horinzontal grid size
     * @param integer vertical grid size
     * @param integer token length
     * @param string grid salt (specific for each application)
     * @return NULL
     *********************************************************************/
    public function TokenGrid($x_grid_size = 10, $y_grid_size = 10, $token_length = 4, $grid_salt = 'T@kenGr!D')
    {
        $this->SetGridSize($x_grid_size,$y_grid_size);
        $this->SetTokenLength($token_length);
        $this->SetGridSalt($grid_salt);
        $this->SetHorizontalGridLabels(); // Initialize this value to the default one
        $this->SetTokenChars();           // Initialize this value to the default one
        $this->SetAvoidSameChars();       // Initialize this value to the default one
        $this->SetNotCaseSensitive();     // Initialize this value to the default one
    }


    /* Set the grid size, default is 10 x 10 */
    function SetGridSize($x_grid_size = 10, $y_grid_size = 10)
    {
        $this->_x_grid_size = $x_grid_size;
        $this->_y_grid_size = $y_grid_size;
    }
    

    /* Set the grid salt, which will generate different series of token grids, even if the grid_id is the same */
    function SetGridSalt($grid_salt = 'T@kenGr!D')
    {
        $this->_grid_salt = $grid_salt;
    }
    

    /* Set the token length, the default is 4 */
    function SetTokenLength($token_length = 4)
    {
        $this->_token_length = (($token_length < 1)?1:(($token_length > 10)?10:$token_length));
    }
    

    /* Set the horizontal labels, the default set has no confusing characters */
    function SetHorizontalGridLabels($horizontal_grid_labels = 'ABCDEFGHJKLMNPQRSTUWXYZ')
    {
        $this->_horizontal_grid_labels = $horizontal_grid_labels;
    }
    

    /* Set the token characters, the default set has no confusing characters */
    function SetTokenChars($token_chars = 'ABCDEFGHJKLMNPQRSTUWXYZ2345679')
    {
        $this->_token_chars = $token_chars;
    }
    

    /* Avoid to have the same characters in a token, this option is enabled by default */
    function SetAvoidSameChars($avoid_same_chars = true)
    {
        $this->_avoid_same_chars = $avoid_same_chars;
    }
    

    /* Do not be case sensitive, this option is activated by default */
    function SetNotCaseSensitive($not_case_sensitive = true)
    {
        $this->_not_case_sensitive = $not_case_sensitive;
    }
    

    /* Check a token by giving the grid position, the grid id and the token to check. (return a boolean value) */
    function IsTokenValid($grid_position, $grid_id, $token_to_check)
    {
        $x_pos = 1 + strpos($this->_horizontal_grid_labels, substr($grid_position, 0, 1));
        $y_pos = intval(substr($grid_position, 1));
      
        if ($this->_not_case_sensitive)
        {
            return (strtoupper($token_to_check) == strtoupper($this->GetToken($x_pos, $y_pos, $grid_id)));
        }
        else
        {
            return ($token_to_check == $this->GetToken($x_pos, $y_pos, $grid_id));
        }
    }
    
    
    /* Get the token value by giving the grid position and the grid id */
    function GetTokenFromGrid($grid_position, $grid_id)
    {
        $x_pos = 1 + strpos($this->_horizontal_grid_labels, substr($grid_position, 0, 1));
        $y_pos = intval(substr($grid_position, 1));
      
        return $this->GetToken($x_pos, $y_pos, $grid_id);
    }
    
    
    /* Get the token value by giving the horizontal and vertical position and the grid id */
    function GetToken($x_pos, $y_pos, $grid_id)
    {
        $validated_x_pos = ((abs(intval($x_pos))-1)%$this->_x_grid_size)+1;
        $validated_y_pos = ((abs(intval($y_pos))-1)%$this->_y_grid_size)+1;
        $token_hash = md5(chr(64+$validated_x_pos).$this->_grid_salt.chr(96+$validated_y_pos).$grid_id);
        
        $current_token = '';
        
        for ($token_position = 0; $token_position < $this->_token_length; $token_position++)
        {
            $current_char = hexdec(substr($token_hash, 2 * $token_position, 2)) % strlen($this->_token_chars);
            if (($this->_avoid_same_chars) && ($this->_token_length < strlen($this->_token_chars)))
            {
                while (false !== strpos($current_token, substr($this->_token_chars, $current_char, 1)))
                {
                    $current_char = ($current_char + 1) % strlen($this->_token_chars);
                }
            }
            $current_token .= substr($this->_token_chars, $current_char, 1);
        }
        return $current_token;
    }
    
    
    /* Get the grid array for a specific grid id */
    function GetGridArray($grid_id)
    {
        for ($line = 0; $line <= $this->_y_grid_size; $line++)
        {
            for ($col = 0; $col <= $this->_x_grid_size; $col++)
            {
                if (0 == $col)
                {
                    if (0 == $line)
                    {
                        $grid_cols[$col] = '';
                    }
                    else
                    {
                        $grid_cols[$col] = $line;
                    }
                }
                else // (0 != $col)
                {
                    if (0 == $line)
                    {
                        $grid_cols[$col] = substr($this->_horizontal_grid_labels, $col - 1, 1);
                    }
                    else
                    {
                        $grid_cols[$col] = $this->GetToken($col, $line, $grid_id);
                    }
                }
            }
            $grid_lines[$line] = $grid_cols;
        }
        return $grid_lines;
    }


    /* Get the grid in Xhtml format for a specific grid id */
    function GetXhtmlGrid($grid_id)
    {
        $xhtml_output = "";
        $grid_array = $this->GetGridArray($grid_id);

        $xhtml_output .= "<table>";
        for ($line = 0; $line <= $this->_y_grid_size; $line++)
        {
            $xhtml_output .= "<tr>";
            for ($col = 0; $col <= $this->_x_grid_size; $col++)
            {
                if (0 == $col)
                {
                    if (0 == $line)
                    {
                        $xhtml_output .= "<th>";
                        $xhtml_output .= "</th>";
                    }
                    else
                    {
                        $xhtml_output .= "<th>";
                        $xhtml_output .= $line;
                        $xhtml_output .= "</th>";
                    }
                }
                else // (0 != $col)
                {
                    if (0 == $line)
                    {
                        $xhtml_output .= "<th>";
                        $xhtml_output .= substr($this->_horizontal_grid_labels, $col - 1, 1);
                        $xhtml_output .= "</th>";
                    }
                    else
                    {
                        $xhtml_output .= "<td>";
                        $xhtml_output .= $grid_array[$line][$col];
                        $xhtml_output .= "</td>";
                    }
                }
            }
            $xhtml_output .="</tr>\n";
        }
        $xhtml_output .="</table>\n";
        
        return $xhtml_output;
    }

    
    /* Get a valid random position in the grid */
    function GetRandomGridPosition($grid_id)
    {
        $x_pos = mt_rand(1,$this->_x_grid_size);
        $y_pos = mt_rand(1,$this->_y_grid_size);
        
        return substr($this->_horizontal_grid_labels, $x_pos - 1, 1).$y_pos;
    }
}

?>