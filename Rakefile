load File.dirname(__FILE__) + "/cruise.rake"

task :default => :test
task :test => %w[test:unit test:integration]

namespace :test do
  desc "run unit tests"
  task :unit do
    sh "phpunit tests/unit"
  end

  desc "run integration tests"
  task :integration do
    sh "phpunit tests/integration"
  end
end

namespace :docs do
  ROOT_DIR = File.dirname(__FILE__)
  DOCS_PATH = File.join(ROOT_DIR, 'docs')

  desc "clean documentation"
  task :clean do
    rm_rf DOCS_PATH
  end

  desc "generate documentation"
  task :generate => :clean do
    mkdir DOCS_PATH

    title = "Braintree PHP Client Library"
    phpdoc_path = File.join('/', 'opt', 'local', 'pear', 'bin', 'phpdoc')
    project_path = File.join(ROOT_DIR, 'lib')
    packages = "Braintree"
    output_format = "HTML"
    converter = "frames"
    template = "earthli"
    private_flag = "off"

    sh "#{phpdoc_path} -d #{project_path} -t #{DOCS_PATH} -ti \"#{title}\" -po #{packages} #{output_format}:#{converter}:#{template} -pp #{private_flag} --ignore \"tests/\",\"Zend/\""
  end
end
