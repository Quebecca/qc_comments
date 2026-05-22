# Changelog — qc/qc-comments

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] — current

### Fixed
- **[Bug] Date filter (userDefined) not working in backend module** — pgu/pgu#9711
  The "custom period" filter (start date / end date) was no longer filtering results
  after upgrading to TYPO3 v13. The TYPO3 v13 date picker (`t3js-datetimepicker`)
  uses flatpickr with `altInput: true`, which creates an internal input field and
  does not propagate the selected date to the Extbase-bound `f:form.hidden` field
  (`property="startDate"` / `property="endDate"`). As a result, Extbase received
  empty date values and `Filter::getDateCriteria()` generated no SQL clause.
  Fixed by adding a JavaScript listener on `formengine.dp.change` in
  `AdministrationModule.js` to synchronize the datepicker value to the hidden
  Extbase-bound field on every date selection.
  Affected template: `Resources/Private/Partials/Filters/CommentsFilters.html`
  Affected JS: `Resources/Public/JavaScript/AdministrationModule.js`

---

## [2.0.1] — current

### Notes
- Compatible with TYPO3 v12.4 and v13.4
- Requires `fluidtypo3/vhs ^7.2` and `phpoffice/phpspreadsheet`

---

## [2.0.0]

### Added
- TYPO3 v13 compatibility
- Backend module with four tabs: Statistics, Comments, Removed Comments,
  Technical Problems
- Date range filter with preset periods (1 day, 1 week, 1 month, etc.)
  and custom date range (userDefined)
- Export to XLSX via `phpoffice/phpspreadsheet`
- Backend session persistence for filter state
- Language filter, depth filter, usability filter, comment reason filter
- Include empty pages option