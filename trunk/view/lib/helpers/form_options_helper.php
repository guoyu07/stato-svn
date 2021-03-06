<?php

function select($objectName, $method, $object, $choices, $options = array(), $htmlOptions = array())
{
    list($name, $value, $htmlOptions) = default_options($objectName, $method, $object, $htmlOptions);
    $optionsBlock = add_select_options(options_for_select($choices, $value), $options, $value);
    return select_tag($name, $optionsBlock, $htmlOptions);
}


function collection_select($objectName, $method, $object, $collection, $valueProp, $textProp, $options=array(), $htmlOptions = array())
{
    list($name, $value, $htmlOptions) = default_options($objectName, $method, $object, $htmlOptions);
    $optionsBlock = add_select_options(options_from_collection_for_select($collection, $valueProp, $textProp, $value), $options, $value);
    return select_tag($name, $optionsBlock, $htmlOptions);
}

function add_select_options($optionsBlock, $options, $value = null)
{
    if ($options['include_blank']) $optionsBlock = '<option value=""></option>'.$optionsBlock;
    if (empty($value) && isset($options['prompt']))
        $optionsBlock = '<option value="">'.$options['prompt'].'</option>'.$optionsBlock;
    return $optionsBlock;
}

function options_for_select($set, $selected=Null)
{
    $str = '';
    if (!is_array($selected)) $selected = array($selected);
    foreach ($set as $lib => $value)
    {
        if (is_numeric($lib)) $lib = $value; // non-associative array
        
        $str.= '<option value="'.html_escape($value).'"';
        if (in_array($value, $selected)) $str.= ' selected="selected"';
        $str.= '>'.html_escape($lib)."</option>\n";
    }
    return $str;
}

function options_from_collection_for_select($collection, $valueProp, $textProp, $selected=null)
{
    $set = array();
    foreach ($collection as $entity) $set[$entity->$textProp] = $entity->$valueProp;
    return options_for_select($set, $selected);
}

function options_groups_from_collection_for_select()
{

}

function country_options_for_select()
{
    return array("Afghanistan", "Albania", "Algeria", "American Samoa", 
    "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua And Barbuda", 
    "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", 
    "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", 
    "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina",
    "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", 
    "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burma", "Burundi", 
    "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", 
    "Central African Republic", "Chad", "Chile", "China", "Christmas Island", 
    "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", 
    "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", 
    "Cote d'Ivoire", "Croatia", "Cyprus", "Czech Republic", "Denmark", 
    "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", 
    "Egypt", "El Salvador", "England", "Equatorial Guinea", "Eritrea", 
    "Espana", "Estonia", "Ethiopia", "Falkland Islands", "Faroe Islands", 
    "Fiji", "Finland", "France", "French Guiana", "French Polynesia", 
    "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", 
    "Ghana", "Gibraltar", "Great Britain", "Greece", "Greenland", "Grenada", 
    "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", 
    "Haiti", "Heard and Mc Donald Islands", "Honduras", "Hong Kong", "Hungary",
    "Iceland", "India", "Indonesia", "Ireland", "Israel", "Italy", "Iran", 
    "Irak", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", 
    "Korea, Republic of", "Korea (South)", "Kuwait", "Kyrgyzstan", 
    "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", 
    "Liberia", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia", 
    "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", 
    "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", 
    "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", 
    "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", 
    "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", 
    "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Ireland", 
    "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", 
    "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", 
    "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russia", "Rwanda", 
    "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", 
    "Samoa (Independent)", "San Marino", "Sao Tome and Principe", "Saudi Arabia", 
    "Scotland", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", 
    "Slovenia", "Solomon Islands", "Somalia", "South Africa", 
    "South Georgia and the South Sandwich Islands", "South Korea", "Spain", 
    "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Suriname", 
    "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", 
    "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tokelau", "Tonga", 
    "Trinidad", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", 
    "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", 
    "United Arab Emirates", "United Kingdom", "United States", 
    "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", 
    "Vatican City State (Holy See)", "Venezuela", "Viet Nam", 
    "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wales", 
    "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Zambia", "Zimbabwe");
}

?>
