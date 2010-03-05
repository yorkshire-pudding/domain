// $Id$

/**
 * @file
 * README file for Domain Settings
 */

Domain Access: Domain Specific Settings
Allows domain specific use of Drupal system settings forms.

CONTENTS
--------

1.  Introduction
1.1   Use-Case
2.  Installation
2.1   Dependencies
2.2   Permissions
2.3   Exceptions
3.  Configuration Options
3.1  Domain Settings Behavior
3.2   Disallowed Forms


----
1.  Introduction

The Domain Access: Domain Settings module, is an optional extension of the
Domain Access module.  Domain Settings allows forms for other modules
to save different settings for each of your affiliate sites.

When enabled a 'Domain-specific settings' section will appear on all applicable
forms allowing the domain to which the settings apply to be selected (see 2.2
for a description of cases where this will not occur). The settings for a
particular domain are revealed by opening the form from that domain.

----
1.1 Use-Case

It is often desireable for affiliates to have domain specific settings. This
was originally addressed using the Domain Configuration module to expose
certain settings through a single UI, but other settings and especially those
from contributed modules could only be included by adding exceptions in
hook_domainconf() or hook_domainbatch(). This is cumbersome for the site
maintainer, and out of scope for the Domain Access project.

Domain Settings makes it so that any form that uses system_settings_form() can
be told to submit changes to a specific domain. For example, if you want to
change the Site Information for one of your affiliates you can go to
/admin/settings/site-information from any of the affiliates, enter the
desired information, select the appropriate affiliate from the 'Domain-specific
settings' section and save the setting for that affiliate.

----
2.  Installation

To install, untar the Domain Settings package and place the entire folder in
your modules directory.

The Domain Settings module does not add any database tables.

----
2.1   Dependencies

Domain Settings requires the Domain Access and Domain Conf modules be installed
and active.

----
2.2    Permissions

The module adds one permission, 'access domain settings form'. Users with
this permission may select which domain a form setting is saved to.

Users without this permission will have settings forms saved for the primary
domain, regardless of the active domain.

----
2.3   Exceptions

This module only works with forms that do not modify the handling of
system_settings_form. As a result certain core forms (e.g., forums, and
date/time) cannot be made domain-specific.

----
3.   Configuration Options

When active, the Domain Settings module provides a 'Domain-specific settings'
section in the settings tab of the Domain Access settings page (found at path
'admin/build/domain/settings'). In this section you can configure the following
behaviors.

----
3.1  Domain Settings Behavior

The default value for which domain a form is saved to when submitting system
settings can be set to either the primary domain or the active domain.

This setting can be very helpful in preventing accidental misconfiguration of
your sites.

----
3.2   Disallowed Forms

Domain-specific settings can be disallowed for particular forms by entering a
list of form_ids, one per line. This option is useful for site administrators
who wish to prevent domain-specific settings on certain forms.

Form ids are an internal Drupal identifier. Check api.drupal.org for the
specific form functions that control settings pages.