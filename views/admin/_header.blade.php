<div class="x_page-header">
    <h1>{{ $lang->blockman }}</h1>
</div>
<ul class="x_nav x_nav-tabs">
    <li @class(['x_active' => $act == 'dispBlockmanAdminConfig'])><a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminConfig') }}">{{ $lang->cmd_blockman_admin_config }}</a></li>
    <li @class(['x_active' => $act == 'dispBlockmanAdminList'])><a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminList') }}">{{ $lang->cmd_blockman_admin_list }}</a></li>
    <li @class(['x_active' => $act == 'dispBlockmanAdminAction'])><a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminAction') }}">{{ $lang->cmd_blockman_admin_action }}</a></li>
</ul>
