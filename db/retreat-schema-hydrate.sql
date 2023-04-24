-- ---------------------------------------------------------------------
-- Add data
-- ---------------------------------------------------------------------

INSERT INTO expense_status (expense_status_id, expense_status_descr) VALUES (1, 'planned'), (2, 'active'), (3, 'ended');
INSERT INTO asset_status (asset_status_id, asset_status_descr) VALUES (1, 'untapped'), (2, 'active'), (3, 'depleted');
INSERT INTO account_type (account_type_id, account_type_descr) VALUES (1, 'expense'), (2, 'asset');

INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('base', 1, null);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('alt', 1, 1);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('base', 2, null);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('alt', 2, 3);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    expense_status_id, repeat_every
) VALUES (
	1, 'mortgage', 1500.00,
    2.225,
    2026, 1, 2999, 12,
    1, null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    expense_status_id, repeat_every
) VALUES (
	1, 'groceries', 500.00,
    3.000,
    2026, 1, 2999, 12,
    1, null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    expense_status_id, repeat_every
) VALUES (
	1, 'cell phone', 125.00,
    -2.000,
    2026, 1, 2999, 12,
    1, null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    expense_status_id, repeat_every
) VALUES (
	1, 'property tax', 14000.00,
    3.000,
    2026, 1, 2999, 12,
    1, 12
);

INSERT INTO expense (
	scenario_id, expense_name, amount,
    expense_status_id
) VALUES (
	2, 'cell phone', 5.00,
    1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, current_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month, 
    asset_status_id
) VALUES (
	3, 'savings_account', 
    1000.00, 1000.00, 510.00, 2.500,
    null, 2026, 1,
    1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, current_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month, 
    asset_status_id
) VALUES (
	3, 'other savings_account', 
    200.00, 200.00, 50.00, 1.000,
    0, null, null,
    1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, current_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month, 
    asset_status_id
) VALUES (
	3, 'social security', 
    250000.00, 250000.00, 5000.00, 2.100,
    null, 2033, 2,
    1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, current_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month, 
    asset_status_id
) VALUES (
	3, 'rsu1', 
    1000000.00, 1000000.00, 500000.00, 3.015,
    null, 2026, 1,
    1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, current_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month, 
    asset_status_id
) VALUES (
	3, 'rsu2', 
    500000.00, 500000.00, 500000.00, 3.015,
    null, 2026, 1,
    1
);

INSERT INTO asset (
	scenario_id, asset_name, max_withdrawal, apr
) VALUES (
	4, 'other savings account', 75.00, 1.5000
);

INSERT INTO asset (
	scenario_id, asset_name, begin_month
) VALUES (
	4, 'rsu2', 3
);
