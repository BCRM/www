Feature: Newsletter
    In order to receive the latest news about the BarCamp RheinMain
    As a user
    I need to be able to manage my newsletter subscription

    Scenarion: Signup for the newsletter
        Given I am on "/"
          And I fill in "email" with "name@domain.com"
          And I click on "submit"
         Then I should see "Please" message
