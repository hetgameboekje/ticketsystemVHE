<?php

namespace App\Core;

/**
 * Herbruikbare tabel-bouwer voor module-overzichten. Bouwt dezelfde markup
 * (.table-wrap > table, met sorteerbare headers via sortLink()) die voorheen
 * in elke module-view apart werd getypt.
 *
 * Gebruik:
 *   $table = (new Table())
 *       ->sortState($sort, $dir)
 *       ->rowUrl(fn (array $r) => "/tickets/{$r['id']}")
 *       ->column('id', '#', fn (array $r) => '#' . $r['id'], ['class' => 'col-1'])
 *       ->column('titel', 'Titel', fn (array $r) => htmlspecialchars($r['titel']))
 *       ->rows($items);
 *   echo $table->render();
 */
class Table
{
    private array $columns = [];
    private array $rows = [];

    /** @var callable|null */
    private $rowUrl = null;

    private ?string $sort = null;
    private string $dir = 'asc';
    private string $emptyText = 'Geen resultaten gevonden.';

    /**
     * @param callable|null $render fn(array $row): string — mag HTML teruggeven (zelf escapen indien nodig).
     *                              Zonder render-callback wordt $row[$key] getoond, htmlspecialchars-veilig.
     * @param array{class?:string, sortable?:bool, stopPropagation?:bool, cellStyle?:string} $options
     */
    public function column(string $key, string $label, ?callable $render = null, array $options = []): static
    {
        $this->columns[] = [
            'key' => $key,
            'label' => $label,
            'render' => $render,
            'class' => $options['class'] ?? '',
            'sortable' => $options['sortable'] ?? true,
            'stopPropagation' => $options['stopPropagation'] ?? false,
            'cellStyle' => $options['cellStyle'] ?? '',
        ];

        return $this;
    }

    public function rows(array $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function sortState(?string $sort, string $dir = 'asc'): static
    {
        $this->sort = $sort;
        $this->dir = $dir;
        return $this;
    }

    /** @param callable $fn fn(array $row): string — URL waar de rij naartoe navigeert bij klikken. */
    public function rowUrl(callable $fn): static
    {
        $this->rowUrl = $fn;
        return $this;
    }

    public function emptyText(string $text): static
    {
        $this->emptyText = $text;
        return $this;
    }

    public function render(): string
    {
        require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

        if (empty($this->rows)) {
            return '<div class="empty-state">' . htmlspecialchars($this->emptyText) . '</div>';
        }

        $html = '<div class="table-wrap"><table><thead><tr>';
        foreach ($this->columns as $col) {
            $class = $col['class'] !== '' ? ' class="' . htmlspecialchars($col['class']) . '"' : '';
            $header = $col['sortable']
                ? sortLink($col['key'], $col['label'], $this->sort, $this->dir)
                : htmlspecialchars($col['label']);
            $html .= "<th{$class}>{$header}</th>";
        }
        $html .= '</tr></thead><tbody>';

        foreach ($this->rows as $row) {
            $rowAttr = '';
            if ($this->rowUrl !== null) {
                $url = htmlspecialchars(($this->rowUrl)($row));
                $rowAttr = " onclick=\"window.location='{$url}'\"";
            }
            $html .= "<tr{$rowAttr}>";

            foreach ($this->columns as $col) {
                $style = $col['cellStyle'] !== '' ? ' style="' . htmlspecialchars($col['cellStyle']) . '"' : '';
                $stop = $col['stopPropagation'] ? ' onclick="event.stopPropagation()"' : '';
                $content = $col['render'] !== null
                    ? ($col['render'])($row)
                    : htmlspecialchars((string) ($row[$col['key']] ?? '—'));
                $html .= "<td{$style}{$stop}>{$content}</td>";
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }
}
