task :default => :test
task :test => %w[test:unit test:integration]

namespace :test do
  desc "print PHP version"
  task :version do
    print_php_version("php")
  end

  # Usage:
  #   rake test:unit
  #   rake test:unit[ConfigurationTest]
  #   rake test:unit[ConfigurationTest,testConstructWithArrayOfCredentials]
  desc "run unit tests"
  task :unit, [:file_name, :test_name] => :version do |task, args|
    if args.file_name.nil?
      sh "php ./vendor/bin/phpunit --testsuite unit"
    elsif args.test_name.nil?
      sh "./vendor/bin/phpunit tests/unit/#{args.file_name}"
    else
      sh "./vendor/bin/phpunit tests/unit/#{args.file_name} --filter #{args.test_name}"
    end
  end

  # Usage:
  #   rake test:integration
  #   rake test:integration[PlanTest]
  #   rake test:integration[PlanTest,testAll_returnsAllPlans]
  desc "run integration tests"
  task :integration, [:file_name, :test_name] do |task, args|
    if args.file_name.nil?
      sh "php ./vendor/bin/phpunit --testsuite integration"
    elsif args.test_name.nil?
      sh "./vendor/bin/phpunit tests/integration/#{args.file_name}"
    else
      sh "./vendor/bin/phpunit tests/integration/#{args.file_name} --filter #{args.test_name}"
    end
  end

  desc "run tests under PHP"
  task :php => %w[php:unit php:integration]
end

desc "update the copyright year"
task :copyright, :from_year, :to_year do |t, args|
  sh "find tests lib -type f -name '*.php' -exec sed -i 's/#{args[:from_year]} Braintree/#{args[:to_year]} Braintree/g' {} +"
end

def print_php_version(interpreter)
  sh "#{interpreter} --version"
end
