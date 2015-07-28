#TouchIt#
[![Travis CI build status](https://travis-ci.org/SuperMarcus/TouchIt.svg)](https://travis-ci.org/SuperMarcus/TouchIt "Last build status on Travis CI")
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/SuperMarcus/TouchIt.svg)](http://isitmaintained.com/project/SuperMarcus/TouchIt "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/SuperMarcus/TouchIt.svg)](http://isitmaintained.com/project/SuperMarcus/TouchIt "Percentage of issues still open")

This is a multifunctional sign system.

###How to use###
For world teleport sign:

Create a sign with format:
>Line1: touchit&w
>
>Line2: [LevelName]
>
>Line3: [Description]
>
>Line4: [Blank]

For portal sign:

Firstly build the departure:
>Line1: touchit&p
>
>Line2: [PortalName]
>
>Line3: [Description]
>
>Line4: [Description]

Then build the arrival:
>Line1: touchit&p
>
>Line2: [PortalName]
>
>Line3: [Description]
>
>Line4: [Description]

(Note: PortalName must be the same as departure)

For command sign:

Create a sign with format:
>Line1: touchit&c
>
>Line2: [Command]
>
>Line3: [Command]
>
>Line4: [Description]

###WARNING###
*This plugin is made for PocketMine-MP 1.4 +, it can not be used on lower version.*
