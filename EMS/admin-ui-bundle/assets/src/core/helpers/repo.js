import $ from 'jquery'

export function formatRepo(repo) {
  if (repo.loading) return repo.text

  return "<div class='select2-result-repository clearfix'>" + repo.text + '</div>'
}

export function formatRepoSelection(repo) {
  let tooltip
  if (Object.hasOwn(repo, 'element') && repo.element instanceof HTMLElement) {
    tooltip = repo.element.dataset.tooltip ?? null
  } else {
    tooltip = repo.tooltip ?? null
  }

  if (tooltip !== null) {
    const item = $('<span data-toggle="tooltip" title="' + tooltip + '">' + repo.text + '</span>')
    item.tooltip()
    return item
  }
  return repo.text
}
