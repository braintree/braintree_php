task :default => :test
task :test => %w[test:unit test:integration]

namespace :test do
  desc "run unit tests"
  task :unit do
    run_php_test_suite("unit")
  end

  desc "run integration tests"
  task :integration do
    run_php_test_suite("integration")
  end
end

def run_php_test_suite(test_suite)
  sh "./vendor/bin/phpunit --testsuite #{test_suite}"
end
