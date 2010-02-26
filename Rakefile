task :default => %w[test:unit test:integration]

namespace :test do
  desc "run unit tests"
  task :unit do
    sh "phpunit tests/unit/AllTests.php"
  end

  desc "run integration tests"
  task :integration do
    sh "phpunit tests/integration/AllTests.php"
  end
end

