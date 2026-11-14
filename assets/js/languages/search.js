/*
 * The language search of the finish setup page and of the spoken languages of the profile: the languages come from
 * language_choices() (App\Service\LanguageChoices) with their own name, their name in the member's language (alt) and
 * their group. A language is found by either name, the ones whose name starts with what is typed first; up to six
 * of each group, spoken languages first, each group under its heading (language_groups()).
 */
const PER_GROUP = 6;

export const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

// languages: [{code, name, alt, group}]; text: what is typed; excluded: a Set of the codes already picked
export function findLanguages(languages, text, excluded) {
    const typed = text.trim().toLowerCase();
    if (!typed) {
        return [];
    }
    const names = (item) => [item.name, item.alt || ''].map((name) => name.toLowerCase());
    const free = languages.filter((item) => !excluded.has(item.code));
    const starts = free.filter((item) => names(item).some((name) => name.startsWith(typed)));
    const contains = free.filter((item) => !starts.includes(item) && names(item).some((name) => name.includes(typed)));
    const found = starts.concat(contains);

    return ['spoken', 'signed'].flatMap((group) => found.filter((item) => item.group === group).slice(0, PER_GROUP));
}

// The list items of the matches, a heading above each group; active: the position of the highlighted match
export function languageResults(matches, active, groups) {
    return matches.map((item, position) => `
        ${position === 0 || matches[position - 1].group !== item.group
            ? `<li class="p-lang-add__group" role="presentation">${escapeHtml(groups[item.group] || '')}</li>`
            : ''}
        <li role="option" class="p-lang-add__result${position === active ? ' is-active' : ''}" data-code="${escapeHtml(item.code)}" lang="${escapeHtml(item.code)}" aria-selected="${position === active}">
            ${escapeHtml(item.name)}
        </li>`).join('');
}
