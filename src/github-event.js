export function isManagedPullRequestMerge({ eventName, commitMessage, repository, branch }) {
  if (eventName !== "push" || !commitMessage || !repository || !branch) {
    return false;
  }

  const [owner] = repository.split("/");
  const firstLine = commitMessage.split("\n", 1)[0];
  const match = /^Merge pull request #\d+ from (.+)$/.exec(firstLine);

  return match?.[1] === `${owner}/${branch}`;
}
