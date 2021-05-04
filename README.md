# oxygenbuilder-conditional-statements
These are added conditional statements that may be used with the Oxygen Builder Plugin.


There are two helper functions: 1. oxext_register_binary_condition This one creates a condition where the user can choose between two values. Such as "Yes" or No, True or False. The first argument is the name of condition, the second is a callback function and the third is the name of the section

The function should return true to show the element or false to hide it 
