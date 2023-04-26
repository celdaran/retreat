select * from account_type;

select * from asset where scenario = 'base';

-- tweaks
update scenario set scenario_parent_id = 1 where scenario_id = 2;
update scenario set scenario_parent_id = 3 where scenario_id = 4;
update scenario set account_type_id = 1 where scenario_id = 2;
update scenario set account_type_id = 2 where scenario_id = 3;

select * from asset;
select * from scenario;

-- fetch an expense scenario
SELECT * FROM account;
SELECT * FROM expense;

-- fetch expenses
SELECT
	s.scenario_id,
	s.scenario_name, at.account_type_descr, '--',
    e.expense_id,
    e.expense_name, e.amount, e.inflation_rate,
    e.begin_year, e.begin_month, e.end_year, e.end_month
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN expense e ON e.scenario_id = s.scenario_id
;

-- fetch assets
SELECT
	s.scenario_id,
	s.scenario_name, at.account_type_descr, '--',
    a.asset_id,
    a.asset_name, a.opening_balance, a.max_withdrawal, a.apr,
    a.begin_after, a.begin_year, a.begin_month
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN asset a ON a.scenario_id = s.scenario_id
;

-- fetch base+alt test
SELECT
	s.scenario_id,
	s.scenario_name, at.account_type_descr, '--',
    a.asset_id,
    a.asset_name, a.opening_balance, a.max_withdrawal, a.apr,
    a.begin_after, a.begin_year, a.begin_month
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN asset a ON a.scenario_id = s.scenario_id
WHERE asset_name = 'rsu2'
;

-- merge on the database side of things
SELECT
    a.asset_name, 
    /*
    max(a.opening_balance),
    max(a.max_withdrawal),
    max(a.apr),
    max(a.begin_after), 
    max(a.begin_year),
    max(a.begin_month),
    */
    SUBSTRING_INDEX(group_concat(a.opening_balance), ',', -1) as opening_balance,
    SUBSTRING_INDEX(group_concat(a.max_withdrawal), ',', -1) as max_withdrawl,
    SUBSTRING_INDEX(group_concat(a.apr), ',', -1) as apr,
    SUBSTRING_INDEX(group_concat(a.begin_after), ',', -1) as begin_after,
    SUBSTRING_INDEX(group_concat(a.begin_year), ',', -1) as begin_year,
    SUBSTRING_INDEX(group_concat(a.begin_month), ',', -1) as begin_month
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN asset a ON a.scenario_id = s.scenario_id
WHERE a.asset_name = 'rsu2'
;

-- find a scenario and its parent (if any) by name
SELECT
	s1.*
FROM scenario s1
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 1
UNION
SELECT
	s2.*
FROM scenario s1
LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 1

ORDER BY 5
;

-- same as above, but with asset added
SELECT
	s1.*, a.*
FROM scenario s1
JOIN asset a ON a.scenario_id = s1.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 2
UNION
SELECT
	s2.*, a.*
FROM scenario s1
LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
JOIN asset a ON a.scenario_id = s2.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 2

ORDER BY scenario_parent_id, asset_id
;

-- same as above, but with scenario removed (since it's just a grouping construct)
SELECT
	a.*
FROM scenario s1
JOIN asset a ON a.scenario_id = s1.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 2
UNION
SELECT
	a.*
FROM scenario s1
LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
JOIN asset a ON a.scenario_id = s2.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 2

ORDER BY asset_name, asset_descr
;

SELECT
	a.asset_name,
    SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) as opening_balance,
    SUBSTRING_INDEX(group_concat(a.max_withdrawal ORDER BY a.asset_id), ',', -1) as max_withdrawl,
    SUBSTRING_INDEX(group_concat(a.apr ORDER BY a.asset_id), ',', -1) as apr,
    SUBSTRING_INDEX(group_concat(a.begin_after ORDER BY a.asset_id), ',', -1) as begin_after,
    SUBSTRING_INDEX(group_concat(a.begin_year ORDER BY a.asset_id), ',', -1) as begin_year,
    SUBSTRING_INDEX(group_concat(a.begin_month ORDER BY a.asset_id), ',', -1) as begin_month
FROM (
	SELECT
		a.*
	FROM scenario s1
	JOIN asset a ON a.scenario_id = s1.scenario_id
	WHERE s1.scenario_name = 'alt'
	  AND s1.account_type_id = 2
	UNION
	SELECT
		a.*
	FROM scenario s1
	LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
	JOIN asset a ON a.scenario_id = s2.scenario_id
	WHERE s1.scenario_name = 'alt'
	  AND s1.account_type_id = 2

	ORDER BY asset_id
) AS a
GROUP BY a.asset_name
ORDER BY a.asset_name
;

-- final Income.php version

select * from scenario;

            SELECT
                a.asset_name AS name,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS opening_balance,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS current_balance,
                SUBSTRING_INDEX(group_concat(a.max_withdrawal ORDER BY a.asset_id), ',', -1) AS max_withdrawal,
                SUBSTRING_INDEX(group_concat(a.apr ORDER BY a.asset_id), ',', -1) AS apr,
                SUBSTRING_INDEX(group_concat(a.begin_after ORDER BY a.asset_id), ',', -1) AS begin_after,
                SUBSTRING_INDEX(group_concat(a.begin_year ORDER BY a.asset_id), ',', -1) AS begin_year,
                SUBSTRING_INDEX(group_concat(a.begin_month ORDER BY a.asset_id), ',', -1) AS begin_month,
                'untapped' AS status
            FROM (
                SELECT
                    a.*
                FROM scenario s1
                JOIN asset a ON a.scenario_id = s1.scenario_id
                WHERE s1.scenario_name = 'alt'
                AND s1.account_type_id = 2
                UNION
                SELECT
                    a.*
                FROM scenario s1
                LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
                JOIN asset a ON a.scenario_id = s2.scenario_id
                WHERE s1.scenario_name = 'alt'
                AND s1.account_type_id = 2
            
                ORDER BY asset_id
            ) AS a
            GROUP BY a.asset_name
            ORDER BY a.asset_name
;

-- 2023-04-26 now to expenses --

SELECT
	e.*
FROM scenario s1
JOIN expense e ON e.scenario_id = s1.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 1
UNION
SELECT
	e.*
FROM scenario s1
LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
JOIN expense e ON e.scenario_id = s2.scenario_id
WHERE s1.scenario_name = 'alt'
  AND s1.account_type_id = 1

ORDER BY expense_id
;

SELECT
	e.expense_name,
    SUBSTRING_INDEX(group_concat(e.amount ORDER BY e.expense_id), ',', -1) as amount,
    SUBSTRING_INDEX(group_concat(e.begin_year ORDER BY e.expense_id), ',', -1) as begin_year,
    SUBSTRING_INDEX(group_concat(e.begin_month ORDER BY e.expense_id), ',', -1) as begin_month,
    SUBSTRING_INDEX(group_concat(e.end_year ORDER BY e.expense_id), ',', -1) as begin_year,
    SUBSTRING_INDEX(group_concat(e.end_month ORDER BY e.expense_id), ',', -1) as begin_month,
    SUBSTRING_INDEX(group_concat(e.repeat_every ORDER BY e.expense_id), ',', -1) as repeat_every
FROM (
	SELECT
		e.*
	FROM scenario s1
	JOIN expense e ON e.scenario_id = s1.scenario_id
	WHERE s1.scenario_name = 'alt'
	  AND s1.account_type_id = 1
	UNION
	SELECT
		e.*
	FROM scenario s1
	LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
	JOIN expense e ON e.scenario_id = s2.scenario_id
	WHERE s1.scenario_name = 'alt'
	  AND s1.account_type_id = 1

	ORDER BY expense_id
) AS e
GROUP BY e.expense_name
ORDER BY e.expense_name
;