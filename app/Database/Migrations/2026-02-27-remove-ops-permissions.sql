START TRANSACTION;

DELETE FROM role_permissions
WHERE permission_id IN (
    SELECT id FROM permissions WHERE name IN ('ops.view', 'ops.manage') OR module = 'ops'
);

DELETE FROM permissions
WHERE name IN ('ops.view', 'ops.manage') OR module = 'ops';

COMMIT;