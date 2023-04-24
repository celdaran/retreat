select * from account_type;

select * from expense_status;

select * from asset_status;

select * from asset where scenario = 'base';

-- tweaks
update scenario set scenario_parent_id = 1 where scenario_id = 2;
update scenario set scenario_parent_id = 3 where scenario_id = 4;
update scenario set account_type_id = 1 where scenario_id = 2;
update scenario set account_type_id = 2 where scenario_id = 3;
update expense set expense_status_id = 1 where expense_id = 5;

select * from asset;
update asset set asset_status_id = 1 where asset_status_id is null;

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
    e.begin_year, e.begin_month, e.end_year, e.end_month,
    es.expense_status_descr
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN expense e ON e.scenario_id = s.scenario_id
JOIN expense_status es ON es.expense_status_id = e.expense_status_id
;

-- fetch assets
SELECT
	s.scenario_id,
	s.scenario_name, at.account_type_descr, '--',
    a.asset_id,
    a.asset_name, a.opening_balance, a.current_balance, a.max_withdrawal, a.apr,
    a.begin_after, a.begin_year, a.begin_month,
    ast.asset_status_descr
FROM scenario s
JOIN account_type at ON at.account_type_id = s.account_type_id
JOIN asset a ON a.scenario_id = s.scenario_id
LEFT JOIN asset_status ast ON ast.asset_status_id = a.asset_status_id
;
