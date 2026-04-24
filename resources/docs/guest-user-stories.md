# Guest User Stories — STB Inventory Portal

## Overview
The STB Inventory Portal exposes a public, read-only dashboard so unauthenticated guests can view aggregated Social Technology analytics and filter by location and year. Guests cannot modify master data or access authenticated modules; they may register or log in to request full access.

## User Stories (with human-readable computations)

- As a Guest, I want to open the portal landing page so that I can view the public dashboard without logging in.
	- What I see (computed): large KPI tiles summarizing the dataset. Example tiles and their plain-language computations are listed below.

- As a Guest, I want to filter dashboard data by region, province, city/municipality, and year so that I can explore Social Technology distribution for specific locations and timeframes.
	- What I see (computed): when I apply filters the dashboard updates these counts immediately:
		- Region ST Title Count: "How many Social Technology titles exist in the selected region(s)" — computed by counting rows that match the selected region(s).
		- Available Years: "Which MOA years have records in this slice" — computed by collecting unique year values from the filtered rows.

- As a Guest, I want to view charts, maps, and summary analytics so that I can understand trends and high-level metrics without an account.
 - As a Guest, I want to see the Total Memorandum of Agreement (Total MOA): "Number of ST records that have an MOA", shown as a count based on my current filters (or the overall total when no filters are applied).
	 - How it's computed: count every row in the current filter where the MOA field is marked (truthy or numeric).

 - As a Guest, I want to see the Total SB Resolution (Total Res): "Number of ST records with a local resolution", shown as a count based on my current filters (or the overall total when no filters are applied).
	 - How it's computed: count every row where the resolution field is marked (truthy or numeric). DB overrides from `RegionItem` may fill missing values.

 - As a Guest, I want to see the Total Expression of Interest (Total Expr): "Number of ST records showing an expression of interest", shown as a count based on my current filters (or the overall total when no filters are applied).
	 - How it's computed: count every row where the expression/interest field is marked (truthy).

 - As a Guest, I want to see the Total Adopted and Replicated: "Sum of adopted counts plus replicated counts across all filtered rows", shown as a sum based on my current filters (or the overall total when no filters are applied).
	 - How it's computed: for each row, take the adopted number (if present) or treat a marked cell as 1; do the same for replicated; then add these per-row values and sum across rows.

 - As a Guest, I want to see Yearly breakdowns (per-year totals and status): "For each year show total STs, ongoing and dissolved counts", computed for the current filters (or overall when no filters are applied).
	 - How it's computed: group filtered rows by MOA year then count total rows and inspect the status columns (or status text) to tally ongoing/dissolved.

- As a Guest, I want to download or view supporting attachments (PDFs) linked from dashboard items so that I can inspect source documents.
	- What I see (computed): the "Uploaded MOA attachments" count and per-row `attachment_url` links where available; computed by matching `StsAttachment` records to parsed rows by region/province/municipality/title/year.

- As a Guest, I want to see clear login and register actions so that I can request an account or sign in for more privileges.

- As a Guest, I want the dashboard to display a guest-friendly UI (filter dock, mobile fallback) so that filters are usable on all devices.

- As a Guest, I want the system to restrict master data and admin functions so that I cannot change records or see admin controls.

- As a Guest, I want privacy-safe behavior (no personal data editing or sensitive actions) so that public access is read-only and safe.


---

## Dashboard Insights — User Stories & Computations

- As a Guest, I want to see Operational Status tiles: "Ongoing STs" and "Inactive STs" so I can quickly understand current implementation status in the selected scope.
	- Computation (plain): count rows where the status indicates ongoing; count rows where status indicates inactive/dissolved. If a sheet provides numeric status columns use those values, otherwise treat truthy cells/text as 1.
	- Share: % ongoing = ongoing / (ongoing + inactive) * 100 (shown on donut).

- As a Guest, I want to see Adoption Status tiles: "Replicated STs" and "Adopted STs" so I know how many titles were replicated or formally adopted.
	- Computation (plain): `Replicated` = sum(normalizeCount(with_replicated)) across filtered rows; `Adopted` = sum(normalizeCount(with_adopted)).
	- Share: % replicated = replicated / (replicated + adopted) * 100.

- As a Guest, I want a Trend Overview (line chart) that plots "Ongoing vs Inactive" over time so I can view status movement by year.
	- Computation (plain): group rows by MOA year; for each year sum ongoing counts and inactive/dissolved counts (use region-specific status columns if present, else parse status text).

- As a Guest, I want a Year-of-MOA bar chart showing MOA counts per year so I can identify peak years and trends.
	- Computation (plain): group rows by `year_of_moa` and count rows (or attachments depending on chart variant) per year.
	- Peak Year: year with the highest MOA count.
	- Average Volume: total MOA count / number of years present (rounded as desired).
	- Latest Year: most recent year value present in data.
	- Coverage Span: number of distinct years represented in the MOA data (or maxYear - minYear + 1 if you prefer calendar span).

- As a Guest, I want Share Analysis donuts (e.g., Ongoing vs Inactive, Replicated vs Adopted) so I can see proportions at a glance.
	- Computation (plain): compute two-part percentages from the underlying counts and render as donut segments with labels and numeric values.

- As a Guest, I want to click a region or KPI to drill down to details (list of ST implementations) so I can inspect locations, years, status, and attachments.
	- Computation (plain): the drilldown lists the filtered `data` rows with columns: `title`, `province`, `municipality`, `year_of_moa`, `status`, `attachment_url`.

- As a Guest, I want quick summary cards (Peak Year, Average Volume, Latest Year, Coverage Span) so I can read quick context for the charts.
	- Computation (plain): derived from the grouped year counts (see Peak Year, Average Volume, Latest Year, Coverage Span above).

- As an Admin, I want export and data-management actions available on the dashboard so I can download the underlying dataset or refresh source spreadsheets.
	- Actions: CSV/XLS export of the current filtered rows, prewarm cache endpoint (`/streport/prewarm`), refresh from Google Sheet endpoint (admin-only).

### Where these are implemented
- Aggregation & grouping: [app/Http/Controllers/MainReportController.php](app/Http/Controllers/MainReportController.php#L1-L220)
- Attachment linking: `addAttachmentInfo()` in `MainReportController` (matches `StsAttachment` to rows)
- Chart counts & caching: [app/Http/Controllers/ExcelController.php](app/Http/Controllers/ExcelController.php#L370-L520)
- UI & drilldown: [resources/views/dashboard/mainreports/_streportContent.blade.php](resources/views/dashboard/mainreports/_streportContent.blade.php#L2340-L2520)


---

## Regional Overview — User Stories (guest + auth) and computations

- As a Guest, I want to open a Region Overview page so I can see regional KPIs, a region map card, and a listing of ST Titles for that region.
	- Visible computations: `Total STs` (count of rows for the region), `Unique Titles` (count distinct titles), `MOA Attachments` (count matched attachments), `Total MOA`, `SB Resolutions`, `Total Adopted`, `Total Replicated` computed for the selected region and filters.

- As a Guest, I want to filter the Region Overview by Province, City, and Year so I can focus on local data.
	- Visible computations: when filters are applied, all KPIs on the page update to reflect the filtered subset (e.g., `Total STs in Province X for Year Y`).

- As a Guest, I want to see ongoing/inactive status tiles so I can quickly know how many implementations are currently ongoing vs inactive in this region.
	- How it's computed: count rows for the region/filter where the 'ongoing' status column is true/numeric, and count rows where 'dissolved'/'inactive'/'completed' is indicated; fall back to parsing status text if dedicated columns are missing.

- As a Guest, I want a region metrics chart that shows uploaded MOA, total MOA, SB Resolutions and other series over time so I can understand trend by year.
	- How it's computed: chart base = [uploaded MOA attachments, total MOA, SB Resolutions, expressions], per-year series built by grouping filtered rows by year and applying the same counts per year.

- As a Guest, I want an ST Titles panel listing each title with its count so I can explore which STs are most common in the region.
	- How it's computed: group filtered rows by `title` and count occurrences; display counts alongside titles.

- As a Guest, I want to click a title to expand details (list of implementations) so I can inspect location, year, status, and attachments for each implementation.
	- What I see: per-row details derived from the `data` rows (province, municipality, year_of_moa, status, attachment_url if present).

- As a Guest, I want to download or open MOA attachments from region rows so I can read source documents.
	- How it's computed: `addAttachmentInfo()` finds attachments matching region/province/municipality/title/year and exposes `attachment_url` links for rows where `action === 'added'`.

- As a Regional Monitor (authenticated), I want edit links next to region rows and titles within my assigned region so I can update master data for records I own.
	- Permissions: edit actions are shown only when authenticated user has write access (MasterDataWriteAccess / admin/sysadmin middleware); guest users do not see edit links.

- As an Admin, I want to see full region controls (import/export, title management) on the Regional Overview so I can maintain master reference data.

### Human-readable computations summary (Regional)

- `Total STs` = count(rows where `region` == selectedRegion AND matches filters).
- `Unique Titles` = count(distinct `title` values in the filtered rows).
- `MOA Attachments` = count of `StsAttachment` records matched to rows (latest added attachment per row counted once).
- `Total MOA` = count(filtered rows where `with_moa` is truthy).
- `SB Resolutions` = count(filtered rows where `with_res` is truthy or DB region item sets `with_res`).
- `Total Adopted` / `Total Replicated` = sum of normalized adopted/replicated counts across filtered rows (numeric or 1 if marked).
- `Ongoing` / `Inactive` = derived from region-specific header indices (if present) or parsed `status` text; counts are aggregated across filtered rows.

### Acceptance & pointers

- Acceptance: the Region Overview updates all KPIs when region/province/city/year filters change; `ST Titles` counts and the detail lists match server-provided `data` rows and `yearStats` for the selected slice.
- Verify code paths: `MainReportController::index()` (aggregation/merge), `addAttachmentInfo()` (attachment linking), and the view partials in [resources/views/dashboard/mainreports/_streportContent.blade.php](resources/views/dashboard/mainreports/_streportContent.blade.php#L2340-L2520) (UI rendering and client-side chart code).

---

## Additional Panels — User Stories & Computations

- As a Guest, I want to see the Regional Year Heatmap so I can quickly spot which regions had activity in which years.
	- Computation (plain): for each region and year, count rows that match; intensify color by count; group missing years under "Unknown".

- As a Guest, I want to see the Title Composition (share of ST Titles) chart so I can understand the distribution of titles.
	- Computation (plain): for each title compute `count(title)` and `percent = count(title)/total_rows * 100`; present top N titles and aggregate the rest as "Other".

- As a Guest, I want the Reference Listing (ST Titles) with counts and percentages so I can browse the title catalog and its contribution to totals.
	- Computation (plain): order titles by descending count; show `count` and `percent = (count/total)*100`; provide pagination and search.

- As a Guest, I want the Social Technology Totals Snapshot (bar chart) showing expression, resolution, MOA, status, replication, and adoption totals so I can compare KPIs side-by-side.
	- Computation (plain): compute each KPI over the current filter using the same normalization rules (counts/sums); display highest and lowest coverage quick facts.

- As a Guest, I want Geographic Reach (Top Regions) and Local Concentration (Top Provinces) lists so I can identify where records are concentrated.
	- Computation (plain): group rows by `region` (or `province`), compute `count` and `percent = count/total * 100`, list top 5 entries.

- As a Guest, I want quick metric cards (Peak Year, Average Volume, Latest Year, Coverage Span) derived from MOA year distribution so I can get concise context.
	- Computation (plain): Peak Year = year with max count; Average Volume = total MOA / number of years present; Latest Year = max year value; Coverage Span = number of distinct years.

- As a Guest, I want to click a chart slice or list item to filter the dashboard so I can jump from insight to detail.
	- Computation (plain): interactive clicks apply the same server-side filters (region/province/year/title) and the controller recomputes aggregates for the new slice.

### Verification pointers

- Heatmap, title composition and snapshot values are assembled from parsed rows in `MainReportController::getParsedData()` and aggregated in `index()`.
- Chart caching and sheet counts: `ExcelController::chartData()`.
- UI rendering and interactivity: [resources/views/dashboard/mainreports/_streportContent.blade.php](resources/views/dashboard/mainreports/_streportContent.blade.php#L2340-L2520).

---

## Directory / Search & Listing — User Stories & Computations

- As a Guest, I want a searchable directory of Social Technology implementations so I can find implementations by ST title or keywords.
	- Explanation: a server-filtered, paginated listing that updates the `Filtered Results` count and summary tiles based on the current search query and active filters (i.e., `Filtered Results` = number of rows matching the search text and selected filters).

- As a Guest, I want to see quick summary tiles above the directory showing `Filtered Results`, `Ongoing`, `Inactive`, and `With Adoption/Replication` so I can get instant counts for the current listing.
	- Computation: each tile is computed over the filtered dataset: `Ongoing` = count(rows with ongoing status), `Inactive` = count(rows with inactive/dissolved status), `With Adoption/Replication` = sum(normalizeCount(with_adopted) + normalizeCount(with_replicated)) across filtered rows.

- As a Guest, I want to filter the directory by status and type so I can narrow down the listing to items of interest.
	- Visible behavior: dropdowns or pills for status/type apply server-side filters and update counts and the table.

- As a Guest, I want to view the listing table with columns (Title, Province, City/Municipality, Status, Attachment) so I can scan entries and click into details.
	- Visible behavior: each row shows status badges (Ongoing/Inactive/Replicated/Adopted) and an attachment column if a public file exists.
	- Computation: status badges are derived from row fields (`status`, `with_adopted`, `with_replicated`); attachment availability is set when `attachment_url` exists.

- As a Guest, I want pagination controls and page-size options so I can navigate large result sets.
	- Visible behavior: Prev/Next and page number UI; server returns the correct slice of rows for the requested page.

- As a Guest, I want to click a row to open details or open the ST profile so I can inspect location, year, full status, and attachments.
	- Visible behavior: clicking either navigates to a details view or opens a modal populated from the server with the row's `data` fields.

- As a Guest, I want the directory search and filters to be bookmarkable/shareable (query params) so I can share specific filtered views with others.
	- Behavior: the UI updates the URL with query parameters (`q`, `region`, `province`, `year`, `status`, `page`) and the controller reads these to produce the same filtered results.

- As an Admin, I want export and bulk actions on the directory so I can download current filtered rows or perform admin operations.
	- Actions: CSV/XLS export of filtered rows, bulk selection for admin-only operations (delete, tag, assign), available only when authenticated and authorized.

### Where to verify

- Listing data and search handling: `MainReportController::titleListingAjax()` and other AJAX endpoints used by the listing UI.
- Tiles and counts: `MainReportController::index()` view variables (`totalExpr`, `totalMoa`, `totalRes`, `yearStats`, `totalAdopted`, `totalReplicated`, etc.).
- Attachment links: `addAttachmentInfo()`.


---

## Regional Monitor — User Stories & Computations

- As a Regional Monitor, I want to register an account so that I can request access to the STB Inventory Portal.
- As a Regional Monitor, I want my account to go through an approval process so that only authorized users can access the system.
- As a Regional Monitor, I want to receive notification of my account approval or rejection so that I am informed of my registration status.
- As a Regional Monitor, I want to log in using approved credentials so that I can access the authorized features of the system.
- As a Regional Monitor, I want to view the dashboard so that I can monitor Social Technology data within my assigned area.
- As a Regional Monitor, I want to filter dashboard data by region, province, city/municipality, and year so that I can analyze Social Technology distribution based on location and timeline.
- As a Regional Monitor, I want to view analytics related to Social Technologies so that I can support monitoring and decision-making.
- As a Regional Monitor, I want to create and update master data records so that Social Technology information in my assigned area can be encoded into the system and kept accurate.
- As a Regional Monitor, I want to use only the approved Social Technology Titles so that encoded records remain standardized and consistent.
- As a Regional Monitor, I want updates in the master data module to automatically reflect in the dashboard so that dashboard analytics remain accurate and real-time.
- As a Regional Monitor, I want to update MOA year, status, and classification details so that Social Technology records reflect their latest implementation status.
- As a Regional Monitor, I want to upload supporting attachments such as MOA documents so that related records have complete reference files in the system.
- As a Regional Monitor, I want to download and view attachments so that I can verify supporting documents.
- As a Regional Monitor, I want to work only within my authorized access and assigned data scope so that data management remains secure and controlled.
- As a Regional Monitor, I want to access the dashboard after logging in so that I can view data specific to my assigned region and scope.
- As a Regional Monitor, I want to see Total MOA so that I can track how many Social Technologies have formal agreements (Computation: count rows where `with_moa` is truthy within assigned scope and active filters).
- As a Regional Monitor, I want to see Total SB Resolution so that I can monitor local government support (Computation: count rows where `with_res` is truthy within assigned scope and active filters).
- As a Regional Monitor, I want to see Total Expression of Interest so that I can assess potential adoption (Computation: count rows where `with_expr` is truthy within assigned scope and active filters).
- As a Regional Monitor, I want to see Total Adopted and Replicated so that I can evaluate implementation reach (Computation: sum normalized adopted + replicated values across filtered rows within assigned scope).
- As a Regional Monitor, I want to view Yearly breakdowns so that I can monitor trends over time (Computation: group rows by `year_of_moa` within assigned scope and compute totals and status counts per year).
- As a Regional Monitor, I want to view Operational Status (Ongoing vs Inactive) so that I can quickly assess implementation status (Computation: count rows by status fields or parsed text within assigned scope and filters).
- As a Regional Monitor, I want to view Adoption Status (Adopted vs Replicated) so that I can understand implementation types and share proportions.
- As a Regional Monitor, I want to see charts (trend lines, bar charts, donut charts) so that I can visualize patterns and performance; I want to interact with charts and KPIs (drilldown) to list detailed records behind aggregates.
- As a Regional Monitor, I want to access a Region Overview page so that I can see KPIs, maps, ST Title distributions, and detailed listings for my assigned region (Visible computations: Total STs, Unique Titles, MOA Attachments, MOA counts, resolutions, adoption metrics).
- As a Regional Monitor, I want to filter the Region Overview by province, city/municipality, and year so that I can focus on localized data.
- As a Regional Monitor, I want to view ST Title distribution so that I can identify commonly implemented technologies (Computation: group by `title` and count occurrences within assigned scope and filters).
- As a Regional Monitor, I want to click a title to view implementation details so that I can inspect records (location, year, status, attachments).
- As a Regional Monitor, I want to search Social Technology implementations so that I can quickly find specific records (Computation: filtered results based on keyword + active filters within assigned scope; paginated).
- As a Regional Monitor, I want dashboard data to automatically update when master data changes so that analytics are always accurate and near real-time (Behavior: writes invalidate/prewarm caches and controllers recompute aggregates).
- As a Regional Monitor, I want filtered results and search to be bookmarkable/shareable so I can reference or send specific views (Behavior: UI updates URL with `q`, `region`, `province`, `year`, `status`, `page`).
- As a Regional Monitor, I want to operate only within my authorized regions so that permissions and data scope are enforced by middleware and controller filters.

Acceptance & verification: registration and approval flows, middleware-enforced scopes, aggregation logic, `addAttachmentInfo()` linking, and cache prewarm endpoints should be tested; verify `app/Http/Controllers/Auth/*`, `app/Http/Middleware/*`, `app/Http/Controllers/MainReportController.php`, and `/streport/prewarm`.

---

## Admin — User Stories (plain language)

- As an Admin, I want to log in securely so that I can access administrative functions of the STB Inventory Portal.
- As an Admin, I want to review newly registered users so that I can determine whether they should be granted access.
- As an Admin, I want to approve or reject user registration requests so that only authorized users can use the system.
- As an Admin, I want to assign user roles during approval so that each account receives the correct level of access.
- As an Admin, I want to manage user accounts so that system access remains correct and up to date.
- As an Admin, I want to reset user passwords so that users who forget credentials can regain access.
- As an Admin, I want to deactivate user accounts so that inactive or unauthorized users can no longer access the system.
- As an Admin, I want to create and maintain approved Social Technology Titles so that master data entries use standardized naming.
- As an Admin, I want to manage sector information (names, images, descriptions) so that the dashboard displays accurate, meaningful content.
- As an Admin, I want to create and update master data so that the dataset remains complete and correct.
- As an Admin, I want approved master-data updates to appear automatically in the dashboard so reports stay accurate.
- As an Admin, I want to monitor data consistency and system activity so that the integrity of Social Technology information is maintained.
- As an Admin, I want to enforce standardized data and access rules so that the system remains secure and well governed.

Acceptance: admins can perform the above actions via the admin UI; user lifecycle and master-data changes propagate to the dashboard views.

Admin — Dashboard details (plain language)

- As an Admin, I want to view the full dashboard so I can see nationwide Social Technology metrics and trends.
- As an Admin, I want to filter the dashboard by region, province, city/municipality, and year so I can inspect data at any level.
- As an Admin, I want to see key metrics at a glance (Total MOA, Total Resolutions, Expressions of Interest, Adopted, Replicated) so I can assess overall program reach.
- As an Admin, I want to view yearly trends and operational status (Ongoing vs Inactive) so I can monitor changes over time.
- As an Admin, I want to click any KPI or chart to drill down into the underlying records so I can investigate specific items.
- As an Admin, I want to export filtered datasets so I can share or analyze the data externally.
- As an Admin, I want master-data edits and approved updates to reflect immediately in dashboard reports so decisions are made on current information.
- As an Admin, I want to monitor attachments and source documents so I can verify evidence for reported entries.
- As an Admin, I want audit or activity logs for administrative actions so I can review changes and maintain accountability.
- As an Admin, I want alerts or notifications for important data issues (duplicates, missing attachments, inconsistent records) so I can prioritize cleanup work.

Acceptance: admins can access and filter the full dashboard, drill into records, export data, and see updates reflected after master-data changes; audit logs and alerts are available for oversight.

Computation (plain language):

- Total MOA: count of records with a signed MOA in the current filtered view.
- Total Resolutions: count of records with a local government resolution in the current filtered view.
- Expressions of Interest: count of records marked as expressing interest in adoption/replication in the current filtered view.
- Adopted / Replicated totals: sum of adopted and replicated values across records in the current filtered view (treat marked-as-adopted/replicated as one where numeric is not provided).
- Yearly breakdowns: group records visible in the current filters by MOA year and show totals and status counts for each year.
- Operational Status (Ongoing vs Inactive): counts of visible records classified as ongoing versus inactive/dissolved in the current filters.
- Uploaded Attachments: count of records that have at least one supporting attachment in the current filtered view.
- Filtered Results: number of records matching the current filters and search terms (used for listing and export).






