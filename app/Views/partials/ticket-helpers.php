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
        $filters = array_diff_key($_GET, ['sort' => null, 'dir' => null, 'q' => null, 'page' => null]);
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

if (!function_exists('filterSelect')) {
    function filterSelect(string $name, string $allLabel, array $options): string
    {
        $current = $_GET[$name] ?? '';

        $html = '<select name="' . htmlspecialchars($name) . '" onchange="this.form.submit()">';
        $html .= '<option value="">' . htmlspecialchars($allLabel) . '</option>';
        foreach ($options as $value => $label) {
            $selected = ((string) $value === (string) $current) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $value) . '"' . $selected . '>'
                . htmlspecialchars($label) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}

if (!function_exists('paginationLinks')) {
    function paginationLinks(array $pagination): string
    {
        if ($pagination['totalPages'] <= 1) {
            return '';
        }

        $page = $pagination['page'];
        $totalPages = $pagination['totalPages'];
        $perPage = $pagination['perPage'];
        $total = $pagination['total'];

        $linkFor = function (int $p): string {
            $params = $_GET;
            $params['page'] = $p;
            return '?' . htmlspecialchars(http_build_query($params));
        };

        $html = '<div class="pagination">';
        $html .= $page > 1
            ? '<a class="page-link" href="' . $linkFor($page - 1) . '">&larr;</a>'
            : '<span class="page-link page-link-disabled">&larr;</span>';

        for ($p = 1; $p <= $totalPages; $p++) {
            $start = ($p - 1) * $perPage + 1;
            $end = min($p * $perPage, $total);
            $label = $start . '-' . $end;

            $html .= $p === $page
                ? '<span class="page-link page-link-active">' . $label . '</span>'
                : '<a class="page-link" href="' . $linkFor($p) . '">' . $label . '</a>';
        }

        $html .= $page < $totalPages
            ? '<a class="page-link" href="' . $linkFor($page + 1) . '">&rarr;</a>'
            : '<span class="page-link page-link-disabled">&rarr;</span>';
        $html .= '</div>';

        return $html;
    }
}
