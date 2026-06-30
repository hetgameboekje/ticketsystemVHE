<?php

if (!function_exists('prioBadge')) {
    function prioBadge(string $prio): string
    {
        $labels = ['laag' => 'Laag', 'normaal' => 'Normaal', 'hoog' => 'Hoog', 'kritiek' => 'Kritiek'];
        $label = $labels[$prio] ?? ucfirst($prio);
        return '<span class="prio prio-' . htmlspecialchars($prio) . '"><span class="prio-dot"></span>' . htmlspecialchars($label) . '</span>';
    }
}

if (!function_exists('statusLabel')) {
    function statusLabel(string $status): string
    {
        $labels = [
            'open' => 'Open',
            'in_behandeling' => 'In behandeling',
            'wacht_op_info' => 'Wacht op info',
            'opgelost' => 'Opgelost',
            'gesloten' => 'Gesloten',
            'nieuw' => 'Nieuw',
            'in_overweging' => 'In overweging',
            'goedgekeurd' => 'Goedgekeurd',
            'afgewezen' => 'Afgewezen',
            'uitgevoerd' => 'Uitgevoerd',
            'aangevraagd' => 'Aangevraagd',
            'afgekeurd' => 'Afgekeurd',
            'besteld' => 'Besteld',
            'geleverd' => 'Geleverd',
            'actief' => 'Actief',
            'inactief' => 'Inactief',
        ];
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        return '<span class="badge badge-' . htmlspecialchars($status) . '">' . htmlspecialchars(statusLabel($status)) . '</span>';
    }
}

if (!function_exists('formatDatum')) {
    function formatDatum(?string $datum): string
    {
        if (empty($datum)) {
            return '—';
        }
        return date('d-m-Y', strtotime($datum));
    }
}

if (!function_exists('formatDatumTijd')) {
    function formatDatumTijd(?string $datum): string
    {
        if (empty($datum)) {
            return '—';
        }
        return date('d-m-Y H:i', strtotime($datum));
    }
}

if (!function_exists('sortLink')) {
    function sortLink(string $column, string $label, ?string $currentSort, string $currentDir): string
    {
        $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';

        $params = $_GET;
        unset($params['sort'], $params['dir']);
        $params['sort'] = $column;
        $params['dir'] = $nextDir;

        $arrow = '';
        if ($currentSort === $column) {
            $arrow = '<span class="sort-arrow">' . ($currentDir === 'asc' ? '&uarr;' : '&darr;') . '</span>';
        }

        return '<a class="th-sort" href="?' . htmlspecialchars(http_build_query($params)) . '">'
            . htmlspecialchars($label) . $arrow . '</a>';
    }
}

if (!function_exists('activeFilterChip')) {
    function activeFilterChip(string $routeBase): string
    {
        $filters = array_diff_key($_GET, ['sort' => null, 'dir' => null]);
        $filters = array_filter($filters, fn ($v) => is_scalar($v) && $v !== '');

        if (empty($filters)) {
            return '';
        }

        $parts = [];
        foreach ($filters as $key => $value) {
            $displayValue = ($key === 'status' && function_exists('statusLabel'))
                ? statusLabel((string) $value)
                : (string) $value;
            $parts[] = htmlspecialchars(ucfirst((string) $key)) . ': ' . htmlspecialchars($displayValue);
        }

        return '<a class="filter-chip" href="/' . htmlspecialchars($routeBase) . '">'
            . implode(', ', $parts) . ' &times;</a>';
    }
}
