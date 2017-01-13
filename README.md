# rtbs-plugin-wordpress

Please note, this plugin must support php 5.3
This means no short array syntax

allow_url_fopen=1 is also required

### Release Log
**13/01/2017: Version 4.1.0**
- When plugin loads, show first date with available sessions

**11/01/2017: Version 4.0.0**
- Instead of displaying sessions for current date, lookup next open session
- Change to html structure around datepicker (may break styles)

**22/12/2016: Version 3.4.2**
- Make date formats consistent

**16/12/2016: Version 3.4.1** 
- When date is changed, update showing date correctly
- Show message when there are no tours

**16/12/2016: Version 3.4.0** 
- Stop page refresh when changing dates
- Remove calendar icon

**12/12/2016: Version 3.3.0** 
- Rename .container class due to conflict
- Use bootstrap glyphicon calendar instead of font-awesome icon
- Improved Expose to API error message
- Fix page redirect for calendar, when not using permalinks

**06/12/2016: Version 3.2.0** 
- Improvements to settings screen
- Include Bootstrap Option
- Improved error messages
- Render Line Breaks in content
- Fix for pickup points
- Add Comments Fields

**02/12/2016: Version 3.1.0** 
- Fixed 0-pax error (No Bootstrap).
- Error message improved if no units selected.

**01/12/2016: Version 3.0.0** 
- Free-text Custom Fields (No Bootstrap).
- Free-text (only) Custom Fields linked to Activities added. Any other field types (i.e Select/Multi-select) will display as free-text fields. No support (yet) for Custom Fields linked to Prices.

**14/10/2016: Version 2.1.0 **
- Bootstrap-free version of 2.0.0
- Bootstrap included in the plugin was conflicting with different version of Bootstrap included in theme on client’s site. Bootstrap-free version provided.

**12/10/2016: Version 2.0.0** 
- Various bug and security fixes
- Email validation updated to match RTBS validation
- Removed hardcoded redirect URL; use setting from field instead
- Removed duplication of tours call
- Removed repeated logins to RTBS API
- Pax Count calculation fixed so over-booking attempt is rejected earlier
- Unit-select droplists made narrower, to give more space for Price IDs.

**11/10/2016: Version 1.0.0** 
- Original release
